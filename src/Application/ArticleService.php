<?php

declare(strict_types=1);

namespace Application;

use Infrastructure\ArticleRepository;
use Infrastructure\HttpFetcher;
use Application\ArticleContentExtractor;
use Config\Paths;
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
        private ArticleContentExtractor $contentExtractor,
        private ArticleRepository $repository
    ) {
        $logger = new Logger('article_extractor');
        $logger->pushHandler(new StreamHandler(Paths::DEBUG_LOG, Level::Debug));

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

        [$title, $content] = $this->contentExtractor->extract($html, $url);

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
}
