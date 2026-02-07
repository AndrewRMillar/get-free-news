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

    public function fetchAndSave(string $url): ?Article
    {
        $this->logger->info('Fetching article (' . __LINE__ . ' ' . __CLASS__ . ')', ['url' => $url]);

        $html = $this->fetcher->fetch($url);
        if (!$html) {
            $this->logger->warning('Failed to fetch article (' . __LINE__ . ' ' . __CLASS__ . ')', ['url' => $url]);
            return null;
        }

        [$title, $content] = $this->contentExtractor->extract($html, $url);

        $this->logger->info('Article extraction complete (' . __LINE__ . ' ' . __CLASS__ . ')', ['title' => mb_substr($title, 0, 25)]);

        $article = new Article(
            0,
            $title,
            $url,
            $content,
            date('c')
        );

        $isNewArticle = $this->repository->save($article);

        if ($isNewArticle) {
            $this->logger->info('Article saved successfully (' . __LINE__ . ' ' . __CLASS__ . ')', ['url' => $url]);
        } else {
            $this->logger->info('Article not saved, already in database (' . __LINE__ . ' ' . __CLASS__ . ')', ['url' => $url]);
        }

        $this->logger->info('Returning article with this title', ['title' => mb_substr($article->title, 0, 25)]);

        return $article;
    }
}
