<?php

declare(strict_types=1);

namespace Application;

use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Level;
use Monolog\Handler\StreamHandler;
use Config\Paths;
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

        $logger = new Logger('article_extractor');
        $logger->pushHandler(new StreamHandler(Paths::DEBUG_LOG, Level::Debug));

        $this->logger = $logger;
    }


    /**
     * @return array{string|null, string}|null
     */
    public function extract(string $html, string $url): ?array
    {
        $this->logger->info('Starting article extraction (' . __LINE__ . ' ' . __CLASS__ . ')', ['url' => $url]);

        libxml_use_internal_errors(true);

        $dom = new DOMDocument('1.0', 'UTF-8');
        if (!$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html)) {
            $this->logger->error('Failed to load HTML (' . __LINE__ . ' ' . __CLASS__ . ')');
            return null;
        }

        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $parsed = parse_url($url);
        if (!$parsed || empty($parsed['scheme']) || empty($parsed['host'])) {
            $this->logger->error('Invalid URL structure (' . __LINE__ . ' ' . __CLASS__ . ')', ['url' => $url]);
            return null;
        }

        $baseUrl = $parsed['scheme'] . '://' . $parsed['host'];

        // Get the title and possible hero image
        [$title, $titleHtml] = $this->extractHeader($xpath);

        $metaHtml = $this->extractPublishedDate($xpath);

        $section = $this->extractArticleSection($xpath);
        if (!$section) {
            $this->logger->error('Failed to extract article section (' . __LINE__ . ' ' . __CLASS__ . ')');
            return null;
        }

        $section = $this->cleanSection($xpath, $section);
        $section = $this->fixRelativeLinks($xpath, $section, $baseUrl);

        $articleHtml = '<article>'
            . $titleHtml
            . $metaHtml
            . $dom->saveHTML($section)
            . '</article>';

        $this->logger->info('Article extraction complete (' . __LINE__ . ' ' . __CLASS__ . ')', ['url' => $url]);

        return [$title, $articleHtml];
    }

    /**
     * @return array{string|null, string}
     */
    private function extractHeader(DOMXPath $xpath): array
    {
        $title = null;
        $titleHtml = '';
        $src = null;

        $titleNodes = $xpath->query('//head/title');
        $titleNode = $titleNodes !== false ? $titleNodes->item(0) : null;
        if ($titleNode instanceof \DOMNode) {
            $title = trim(explode('|', $titleNode->textContent)[0]);
            $titleHtml .= '<h1 class="text-2xl text-neutral-800 dark:text-white font-bold text-center my-4">'
                . htmlspecialchars($title)
                . '</h1>';
        }

        // Preloaded hero image
        $linkNodes = $xpath->query('//head/meta[@property="og:image"]');
        $link = $linkNodes !== false ? $linkNodes->item(0) : null;

        if ($link instanceof DOMElement) {
            $src = $link->getAttribute('content');

            if ($src) {
                $titleHtml .= '<img class="w-full max-h-96 object-cover my-4 mx-auto" src="';
                $titleHtml .= htmlspecialchars($src) . '"';

                if ($title) {
                    $this->logger->info(
                        'Found title(' . __LINE__ . ' ' . __CLASS__ . ')',
                        [
                            'title' => mb_substr($title, 0, 25)
                        ]
                    );
                    $titleHtml .= ' alt="' . htmlspecialchars($title) . '"';
                }

                $titleHtml .= ' />';
            }
        }

        $this->logger->info('Og image image found (' . __LINE__ . ' ' . __CLASS__ . ')', ['src' => (bool) $src]);

        return [$title, $titleHtml];
    }

    private function extractPublishedDate(DOMXPath $xpath): string
    {
        $metaNodes = $xpath->query('//meta[@property="article:published_time"]');
        if ($metaNodes === false) {
            return '';
        }
        $meta = $metaNodes->item(0);
        if (!$meta) {
            return '';
        }

        if (!$meta instanceof DOMElement) {
            return '';
        }

        try {
            $date = new DateTime($meta->getAttribute('content'));
            $this->logger->info(
                'Published time extracted (' . __LINE__ . ' ' . __CLASS__ . ')',
                [
                    'published_time' => $date->format(DateTime::ATOM)
                ]
            );

            return '<p class="text-sm text-gray-500">'
                . 'Gepubliceerd op: ' . htmlspecialchars($date->format('l j F Y - H:i'))
                . '</p>';
        } catch (Throwable $e) {
            $this->logger->warning('Invalid published_time format (' . __LINE__ . ' ' . __CLASS__ . ')');
            return '';
        }
    }

    private function extractArticleSection(DOMXPath $xpath): ?DOMElement
    {
        $this->logger->info('Start extracting article section (' . __LINE__ . ' ' . __CLASS__ . ')');
        $logMessage = '';

        $mainNodes = $xpath->query('//main');
        $main = $mainNodes !== false ? $mainNodes->item(0) : null;
        if (!$main instanceof \DOMNode) {
            $this->logger->warning('Main element not found (' . __LINE__ . ' ' . __CLASS__ . ')');
            return null;
        }

        $logMessage .= 'Found main element. ';

        $articleNodes = $xpath->query('.//article', $main);
        $article = $articleNodes !== false ? $articleNodes->item(0) : null;
        if (!$article instanceof \DOMNode) {
            $this->logger->warning('Article element not found (' . __LINE__ . ' ' . __CLASS__ . ')');
            return null;
        }
        $logMessage .= 'Found article element. ';

        $sectionNodes = $xpath->query('./section', $article);
        if ($sectionNodes === false || $sectionNodes->length === 0) {
            $sectionNodes = $xpath->query('div[@class*="block-text"]');
        }
        $section = $sectionNodes !== false ? $sectionNodes->item(0) : null;

        $logMessage .= 'Searched for section element. ';

        if (!$section instanceof DOMElement) {
            $this->logger->warning('Section element not found (' . __LINE__ . ' ' . __CLASS__ . ')');
            return null;
        }
        $logMessage .= 'Found section element. ';

        $this->logger->info($logMessage . ' (' . __LINE__ . ' ' . __CLASS__ . ')');

        return $section;
    }

    private function cleanSection(DOMXPath $xpath, DOMElement $section): DOMElement
    {
        $nodesToRemove = $xpath->query('.//aside | .//button | .//*[@aria-hidden="true"]', $section);
        if ($nodesToRemove !== false) {
            /** @var \DOMNode $node */
            foreach ($nodesToRemove as $node) {
                $node->parentNode?->removeChild($node);
            }
        }
        $this->logger->info('Cleaned unwanted elements from article section (' . __LINE__ . ' ' . __CLASS__ . ')');

        return $section;
    }

    private function fixRelativeLinks(DOMXPath $xpath, DOMElement $section, string $baseUrl): DOMElement
    {
        $aNodes = $xpath->query('.//a', $section);
        if ($aNodes === false) {
            return $section;
        }
        foreach ($aNodes as $a) {
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

        $this->logger?->info('Fixed relative links in article section (' . __LINE__ . ' ' . __CLASS__ . ')');

        return $section;
    }
}
