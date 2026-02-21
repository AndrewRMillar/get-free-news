<?php

declare(strict_types=1);

namespace puclic;

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
use Infrastructure\GraphQL\Controller;
use Infrastructure\GraphQL\SchemaBuilder;
use Infrastructure\GraphQL\Type\ArticleType;
use Infrastructure\GraphQL\Type\LinkType;
use Infrastructure\Security\CsrfGuard;
use Domain\Repository\ArticleRepositoryInterface;
use Monolog\Handler\StreamHandler;
use Shared\Container;
use PDO;

require_once __DIR__ . '/../Shared/Container.php';
require_once __DIR__ . '/../vendor/autoload.php';

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
    CsrfGuard::class,
    fn() =>
    new CsrfGuard()
);

$container->set(
    ArticleType::class,
    fn() =>
    new ArticleType()
);

$container->set(
    LinkType::class,
    fn() =>
    new LinkType()
);

$container->set(
    ArticleRepository::class,
    fn() =>
    new ArticleRepository($pdo, $logger)
);

$container->set(
    ArticleRepositoryInterface::class,
    fn() =>
    new ArticleRepositoryInterface($pdo)
);

$container->set(
    HomepageLinkExtractor::class,
    fn() =>
    new HomepageLinkExtractor($logger)
);

/* Application */
$container->set(
    SchemaBuilder::class,
    fn(Container $c) =>
    new SchemaBuilder(
        $c->get(ArticleType::class),
        $c->get(ArticleRepository::class),
        $c->get(ArticleService::class),
        $c->get(LinkType::class),
        $c->get(HttpFetcher::class),
        $c->get(HomepageLinkExtractor::class)
    )
);

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

$container->set(
    Controller::class,
    fn(Container $c) =>
    new Controller(
        $c->get(SchemaBuilder::class)
    ),
);

return $container;
