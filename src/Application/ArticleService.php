<?php

declare(strict_types=1);

namespace Application;

use Infrastructure\ArticleRepository;
use Infrastructure\HttpFetcher;
use Application\ArticleContentExtractor;
use Domain\Model\Article;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Level;
use Monolog\Handler\StreamHandler;

final class ArticleService
{
    private LoggerInterface $logger;

    public function __construct(
        private HttpFetcher $fetcher,
        private ArticleContentExtractor $extractor,
        private ArticleRepository $repository
    ) {
        $logger = new Logger('article_extractor');
        $logger->pushHandler(new StreamHandler($this->getLogPath(), Level::Debug));

        $this->logger = $logger;
    }

    public function fetchAndSave(string $url): string|false
    {
        $this->logger->info('Fetching article', ['url' => $url]);
        $count = $this->repository->fetchCount();

        $html = $this->fetcher->fetch($url);
        if (!$html) {
            $this->logger->warning('Failed to fetch article', ['url' => $url]);
            return false;
        }

        [$title, $content] = $this->extractor->extract($html, $url);

        $article = new Article(
            0,
            $title,
            $url,
            $content,
            date('c')
        );

        $this->repository->save($article);

        $countAfter = $this->repository->fetchCount();
        if ($countAfter > $count) {
            $this->logger->info('Article saved successfully', ['url' => $url]);
        } else {
            $this->logger->info('Article not saved', ['url' => $url]);
        }

        return $content;
    }

    public function getLogPath(): string
    {
        $logDir = dirname(__DIR__) . '/log';

        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
            chmod($logDir, 0775);
        }

        $logFile = $logDir . '/debug.log';

        return $logFile;
    }
}
