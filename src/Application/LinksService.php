<?php

declare(strict_types=1);

namespace Application;

use Infrastructure\HttpFetcher;
use Application\HomepageLinkExtractor;
use Config\Paths;
use DateTime;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Level;
use Monolog\Handler\StreamHandler;

class LinksService
{
    private LoggerInterface $logger;

    public function __construct(
        private HttpFetcher $fetcher,
        private HomepageLinkExtractor $linkExtractor,
    ) {
        $logger = new Logger('article_extractor');
        $logger->pushHandler(new StreamHandler(Paths::DEBUG_LOG, Level::Debug));

        $this->logger = $logger;
    }

    public function fetchLinks(): array
    {
        $this->logger->info('Fetching article links (' . __LINE__ . ' ' . __CLASS__ . ')');

        $html = $this->fetcher->fetch(HomepageLinkExtractor::HOMEPAGE_URL);
        if (!$html) {
            $this->logger->warning('Failed to fetch homepage for links (' . __LINE__ . ' ' . __CLASS__ . ')', ['url' => HomepageLinkExtractor::HOMEPAGE_URL]);
            return [];
        }

        $extractedLinks = $this->linkExtractor->extract($html, 50);

        if (!$extractedLinks) {
            $this->logger->warning('No links extracted from homepage (' . __LINE__ . ' ' . __CLASS__ . ')', ['url' => HomepageLinkExtractor::HOMEPAGE_URL]);
            return [];
        }

        return $extractedLinks ?? [];
    }
}
