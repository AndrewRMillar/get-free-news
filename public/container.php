<?php

declare(strict_types=1);

use Infrastructure\HttpFetcher;
use Infrastructure\ArticleRepository;
use Application\ArticleContentExtractor;
use Application\ArticleService;

return [

    PDO::class => function () {
        return new PDO(
            'sqlite:' . __DIR__ . '/../data/articles.sqlite',
            null,
            null,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    },

    HttpFetcher::class => function () {
        return new HttpFetcher();
    },

    ArticleContentExtractor::class => function () {
        return new ArticleContentExtractor();
    },

    ArticleRepository::class => function ($c) {
        return new ArticleRepository($c[PDO::class]);
    },

    ArticleService::class => function ($c) {
        return new ArticleService(
            $c[HttpFetcher::class],
            $c[ArticleContentExtractor::class],
            $c[ArticleRepository::class]
        );
    },
];
