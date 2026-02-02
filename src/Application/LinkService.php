<?php

declare(strict_types=1);

namespace Application;

use Infrastructure\HttpFetcher;
use Application\HomepageLinkExtrator;
use Config\Paths;
use DateTime;
use Domain\Model\Link;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Level;
use Monolog\Handler\StreamHandler;

class LinkService
{
    private LoggerInterface $logger;

    public function __construct(
        private HttpFetcher $fetcher,
        private HomepageLinkExtrator $linkExtractor,
    ) {
        $logger = new Logger('article_extractor');
        $logger->pushHandler(new StreamHandler(Paths::DEBUG_LOG, Level::Debug));

        $this->logger = $logger;
    }

    public function fetchAndSaveLinks(): array
    {
        $this->logger->info('Fetching article links');

        $html = $this->fetcher->fetch(HomepageLinkExtrator::LINK_XPATH);
        if (!$html) {
            $this->logger->warning('Failed to fetch homepage for links', ['url' => HomepageLinkExtrator::LINK_XPATH]);
            return [];
        }

        $extractedLinks = $this->linkExtractor->extract($html, HomepageLinkExtrator::LINK_XPATH);

        if (!$extractedLinks) {
            $this->logger->warning('No links extracted from homepage', ['url' => HomepageLinkExtrator::LINK_XPATH]);
            return [];
        }

        $link = new Link(
            0,
            (new DateTime())->format('Y-m-d'),
            json_encode($extractedLinks)
        );

        return $extractedLinks ?? [];
    }
}
