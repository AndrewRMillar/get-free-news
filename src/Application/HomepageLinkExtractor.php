<?php

declare(strict_types=1);

namespace Application;

use DOMDocument;
use DOMXPath;
use DOMElement;
use Psr\Log\LoggerInterface;

final class HomepageLinkExtractor
{
    public const HOMEPAGE_URL = 'https://www.volkskrant.nl/';

    public function __construct(
        private LoggerInterface $logger
    ) {}

    /**
     * @return array<int, array{title: string, url: string}>
     */
    public function extract(string $html, int $limit = 50): array
    {
        libxml_use_internal_errors(true);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);

        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // volkskrant teaser links
        $nodes = $xpath->query('//a[contains(@class,"wl-teaser")]');

        $links = [];

        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement || !$node->hasAttribute('href')) {
                continue;
            }

            $title = $this->stripTitle($node->getAttribute('aria-label') ?: 'Geen titel');

            $links[] = [
                'title' => $title,
                'url'   => $node->getAttribute('href'),
            ];

            if (count($links) >= $limit) {
                break;
            }
        }

        $this->logger->info('Homepage links extracted', [
            'count' => count($links)
        ]);

        return $links;
    }

    private function stripTitle(string $title): string
    {
        preg_match('/>([^<]+<)/', $title, $matches);

        return $matches[1];
    }
}
