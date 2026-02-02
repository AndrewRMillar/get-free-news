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
use Dom\XPath;
use Throwable;

class HomepageLinkExtrator
{
    // Code for HomepageLinkExtrator would go here
    public const string LINK_XPATH = 'https://www.volkskrant.nl/';

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
        $url = self::LINK_XPATH;

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

        $links = $xpath->query('a[@class*=\'wl-teaser\']');

        $result = [];
        foreach ($links as $link) {
            if ($link instanceof DOMElement && $link->hasAttribute('href')) {
                $title = strip_tags($link->getAttribute('aria-label')) ?: 'No title';

                $result[] = [
                    'url' => $link->getAttribute('href'),
                    'title' => $title,
                ];
            }
        }

        return $result;
    }
}
