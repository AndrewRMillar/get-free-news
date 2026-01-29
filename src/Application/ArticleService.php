<?php

declare(strict_types=1);

namespace Application;

use Infrastructure\ArticleRepository;
use Infrastructure\HttpFetcher;
use Application\ArticleContentExtractor;
use Domain\Article;

final class ArticleService
{
    public function __construct(
        private HttpFetcher $fetcher,
        private ArticleContentExtractor $extractor,
        private ArticleRepository $repository
    ) {}

    public function fetchAndSave(string $url): string|false
    {
        $html = $this->fetcher->fetch($url);
        if (!$html) {
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

        return $content;
    }
}
