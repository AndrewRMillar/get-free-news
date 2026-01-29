<?php

declare(strict_types=1);

namespace Application;

use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Level;
use Monolog\Handler\StreamHandler;
use DOMDocument;
use DOMXPath;
use DOMElement;
use DateTime;
use Throwable;

final class ArticleContentExtractor
{
    private ?LoggerInterface $logger = null;

    public function __construct(?LoggerInterface $logger = null)
    {
        if ($logger) {
            $this->logger = $logger;
            return;
        }

        $logFile = realpath(__DIR__ . '/../../log') . '/debug.log';

        $logger = new Logger('article_extractor');
        $logger->pushHandler(new StreamHandler($logFile, Level::Debug));

        $this->logger = $logger;

        $this->logger->info('Logger initialized successfully');
    }


    public function extract(string $html, string $url): ?array
    {
        $this->logger->info('Starting article extraction', ['url' => $url]);

        libxml_use_internal_errors(true);

        $dom = new DOMDocument('1.0', 'UTF-8');
        if (!$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html)) {
            $this->logger->error('Failed to load HTML');
            return null;
        }

        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $parsed = parse_url($url);
        if (!$parsed || empty($parsed['scheme']) || empty($parsed['host'])) {
            $this->logger->error('Invalid URL structure', ['url' => $url]);
            return null;
        }

        $baseUrl = $parsed['scheme'] . '://' . $parsed['host'];

        // Get the title and possible hero image
        [$title, $titleHtml] = $this->extractHeader($xpath, $baseUrl);

        $metaHtml = $this->extractPublishedDate($xpath);

        $section = $this->extractArticleSection($xpath);
        if (!$section) {
            $this->logger->error('Failed to extract article section');
            return null;
        }

        $section = $this->cleanSection($xpath, $section);
        $section = $this->fixRelativeLinks($xpath, $section, $baseUrl);

        $articleHtml = '<article>'
            . $titleHtml
            . $metaHtml
            . $dom->saveHTML($section)
            . '</article>';

        $this->logger->info('Article extraction complete');

        return [$title, $articleHtml];
    }

    private function extractHeader(DOMXPath $xpath, string $baseUrl): array
    {
        $title = null;
        $titleHtml = '';

        $titleNode = $xpath->query('//head/title')->item(0);
        if ($titleNode) {
            $title = trim($titleNode->textContent);
            $titleHtml .= '<h1 class="text-2xl text-white font-bold text-center my-4">'
                . htmlspecialchars($title)
                . '</h1>';
        }

        // Preloaded hero image
        $link = $xpath->query('//head/meta[@property="og:image"]')->item(0);

        if ($link instanceof DOMElement) {
            $src = $link->getAttribute('content');

            if ($src) {
                $titleHtml .= '<img class="w-full max-h-96 object-cover my-4 mx-auto" src="';
                $titleHtml .= htmlspecialchars($src) . '"';

                if ($title) {
                    $titleHtml .= ' alt="' . htmlspecialchars($title) . '"';
                }

                $titleHtml .= ' />';
            }
        }

        $this->logger->info('Og image image found', ['src' => (bool) $src]);

        return [$title, $titleHtml];
    }

    private function extractPublishedDate(DOMXPath $xpath): string
    {
        $meta = $xpath->query('//meta[@property="article:published_time"]')->item(0);

        if (!$meta instanceof DOMElement) {
            return '';
        }

        try {
            $date = new DateTime($meta->getAttribute('content'));
            $this->logger->info('Published time extracted', ['published_time' => $date->format(DateTime::ATOM)]);

            return '<p class="text-sm text-gray-500">'
                . 'Gepubliceerd op: ' . htmlspecialchars($date->format('l j F Y - H:i'))
                . '</p>';
        } catch (Throwable $e) {
            $this->logger->warning('Invalid published_time format');
            return '';
        }
    }

    private function extractArticleSection(DOMXPath $xpath): ?DOMElement
    {
        $this->logger->info('Start extracting article section');
        $logMessage = '';

        $main = $xpath->query('//main')->item(0);
        if (!$main) {
            $this->logger->warning('Main element not found');
            return null;
        }
        $logMessage .= 'Found main element. ';

        $article = $xpath->query('.//article', $main)->item(0);
        if (!$article) {
            $this->logger->warning('Article element not found');
            return null;
        }
        $logMessage .= 'Found article element. ';

        $section = $xpath->query('./section', $article)->item(0);
        if (!$section) {
            $section = $xpath->query('div[@class*="block-text"]')->item(0);
        }
        $logMessage .= 'Searched for section element. ';

        if (!$section) {
            $this->logger->warning('Section element not found');
            return null;
        }
        $logMessage .= 'Found section element. ';

        $this->logger->info($logMessage);

        return $section;
    }

    private function cleanSection(DOMXPath $xpath, DOMElement $section): DOMElement
    {
        foreach ($xpath->query('.//aside | .//button | .//*[@aria-hidden="true"]', $section) as $node) {
            $node->parentNode?->removeChild($node);
        }
        $this->logger->info('Cleaned unwanted elements from article section');

        return $section;
    }

    private function fixRelativeLinks(DOMXPath $xpath, DOMElement $section, string $baseUrl): DOMElement
    {
        foreach ($xpath->query('.//a', $section) as $a) {
            if (!$a instanceof DOMElement) {
                continue;
            }

            $href = $a->getAttribute('href');
            if ($href && !str_starts_with($href, 'http')) {
                $a->setAttribute(
                    'href',
                    rtrim($baseUrl, '/') . '/' . ltrim($href, '/')
                );
            }
        }
        $this->logger->info('Fixed relative links in article section');

        return $section;
    }
}
