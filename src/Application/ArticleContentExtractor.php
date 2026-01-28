<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Level;
use Monolog\Handler\StreamHandler;

final class ArticleContentExtractor
{
    private ?LoggerInterface $logger = null;

    public function __construct()
    {
        // Initialize logger if needed
        if (!$this->logger) {
            $logger = new Logger('name');
            $logger->pushHandler(new StreamHandler(__DIR__ . '../../log/debug.log', Level::Debug));
            $this->logger = $logger;
        }
    }


    public function extract(string $html, string $url): string
    {
        $this->logger->info('Extracting content from URL: ' . $url);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        $content = '';
        $xpath = new DOMXPath($dom);
        $this->logger->info('DOM loaded and XPath initialized.' . $dom->childElementCount);

        $parsed = parse_url($url);
        $baseUrl = $parsed['scheme'] . '://' . $parsed['host'];

        $head = $xpath->query('.//head')->item(0);
        $this->logger->info('Head element found: ' . ($head ? 'yes' : 'no'));
        if ($head) {
            // Get the title from the head if possible
            $title = $xpath->query('.//title', $head)->item(0);
            $this->logger->info('Title element found: ' . ($title ? 'yes' : 'no'));
            if ($title) {
                $content .= '<h1 class="text-2xl font-bold my-4">' . htmlspecialchars($title->textContent) . '</h1>';
            }

            // Get the imageSrcSet and imageSizes attributes to build an <img> tag
            $link = $xpath->query('.//link[@rel="preload" and @as="image"]', $head)->item(0);
            $this->logger->info('Preload image link found: ' . ($link ? 'yes' : 'no'));
            if ($link instanceof DOMElement) {
                $imageSrcSet = $link->getAttribute('imageSrcSet');
                $imageSizes = $link->getAttribute('imageSizes');
                if ($imageSrcSet && $imageSizes) {
                    // Build an <img> tag with the imageSrcSet and imageSizes attributes
                    $content .= '<img src="' . htmlspecialchars($baseUrl) . '" srcset="' . htmlspecialchars($imageSrcSet) . '" sizes="' . htmlspecialchars($imageSizes) . " alt=\"{$title} ? {$title} : ''\" />";
                }
            }
        }

        $main = $xpath->query('//main[@id="main"]')->item(0);
        if (!$main) {
            return '<p><em>Geen main-content gevonden.</em></p>';
        }

        $article = $xpath->query('.//article', $main)->item(0);
        if (!$article) {
            return '<p><em>Geen artikel gevonden.</em></p>';
        }

        $section = $xpath->query('./section', $article)->item(0);
        if (!$section) {
            return '<p><em>Geen artikel-body gevonden.</em></p>';
        }

        // Clean up unwanted elements
        foreach ($xpath->query('.//aside | .//button | .//*[@aria-hidden="true"]', $section) as $remove) {
            $remove->parentNode?->removeChild($remove);
        }

        // Fix relative links
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

        return '<article>' . $content . $dom->saveHTML($section) . '</article>';
    }
}
