<?php

declare(strict_types=1);

use Infrastructure\HttpFetcher;
use Infrastructure\ArticleRepository;
use Application\ArticleContentExtractor;
use Application\ArticleService;
use Application\HomepageLinkExtractor;
use Application\LinksService;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Level;
use Config\Paths;
use Monolog\Handler\StreamHandler;
use Shared\Container;

require_once __DIR__ . '/../Shared/Container.php';

$pdo = new PDO(
    'sqlite:' . __DIR__ . '/../data/articles.sqlite',
    null,
    null,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

$logger = new Logger('article_extractor');
$logger->pushHandler(new StreamHandler(Paths::DEBUG_LOG, Level::Debug));

$container = new Container();

/* Infrastructure */

$container->set(
    HttpFetcher::class,
    fn() =>
    new HttpFetcher()
);

$container->set(
    ArticleContentExtractor::class,
    fn() =>
    new ArticleContentExtractor()
);

$container->set(
    ArticleRepository::class,
    fn() =>
    new ArticleRepository($pdo)
);

$container->set(
    HomepageLinkExtractor::class,
    fn() =>
    new HomepageLinkExtractor($logger)
);

/* Application */

$container->set(
    ArticleService::class,
    fn(Container $c) =>
    new ArticleService(
        $c->get(HttpFetcher::class),
        $c->get(ArticleContentExtractor::class),
        $c->get(ArticleRepository::class)
    )
);

$container->set(
    LinksService::class,
    fn(Container $c) =>
    new LinksService(
        $c->get(HttpFetcher::class),
        $c->get(HomepageLinkExtractor::class),
    )
);

return $container;
