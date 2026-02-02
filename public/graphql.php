<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Error\UserError;
use Application\ArticleService;
use Application\ArticleException;
use Infrastructure\ArticleRepository;

$container = require __DIR__ . '/container.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');

session_start();

/** -------------------------------------------------
 * CSRF protection (POST only)
 * ------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $headers = getallheaders();
    $csrfHeader = $headers['X-CSRF-Token'] ?? '';

    if (!isset($_SESSION['csrf_token']) || $csrfHeader !== $_SESSION['csrf_token']) {
        http_response_code(403);
        echo json_encode([
            'errors' => [['message' => 'Invalid CSRF token']]
        ]);
        exit;
    }
}


/**-------------------------------------------------
 * Services
 * ------------------------------------------------- */
$repository = $container->get(ArticleRepository::class);
$articleService = $container->get(ArticleService::class);


/**-------------------------------------------------
 * GraphQL Types
 * ------------------------------------------------- */
$articleType = new ObjectType([
    'name' => 'Article',
    'fields' => [
        'id' => Type::int(),
        'title' => Type::string(),
        'url' => Type::string(),
        'content' => Type::string(),
        'publishedAt' => [
            'type' => Type::string(),
            'resolve' => fn($article) => $article->publishedAt,
        ],
    ],
]);

$linkType = new ObjectType([
    'name' => 'Link',
    'fields' => [
        'date' => Type::string(),
        'url' => Type::string(),
        'title' => Type::string(),
    ],
]);

$queryType = new ObjectType([
    'name' => 'Query',
    'fields' => [
        'article' => [
            'type' => $articleType,
            'args' => [
                'id' => Type::nonNull(Type::int()),
            ],
            'resolve' => fn($root, array $args) =>
            $repository->findById($args['id']),
        ],

        'articles' => [
            'type' => Type::listOf($articleType),
            'resolve' => fn() => $repository->findAll(),
        ],

        'links' => [
            'type' => Type::listOf($linkType),
            'resolve' => fn() => $articleService->fetchAndSaveLinks([]),
        ],
    ],
]);

$mutationType = new ObjectType([
    'name' => 'Mutation',
    'fields' => [
        'fetchArticle' => [
            'type' => $articleType,
            'args' => [
                'url' => Type::nonNull(Type::string()),
            ],
            'resolve' => function ($root, array $args) use ($articleService, $repository) {
                try {
                    $content = $articleService->fetchAndSave($args['url']);

                    if ($content === false || !$content) {
                        throw new ArticleException('Could not read article.');
                    }

                    return $repository->findLast();
                } catch (ArticleException $e) {
                    throw new UserError(
                        $e->getMessage(),
                        0,
                        null,
                        null,
                        null,
                        ['code' => $e->codeKey]
                    );
                } catch (Throwable $e) {
                    error_log($e->getMessage());
                    error_log($e->getTraceAsString());

                    throw new UserError(
                        'An error occurred while processing the article.',
                        0,
                        null,
                        null,
                        null,
                        ['code' => 'INTERNAL_ERROR']
                    );
                }
            },
        ],
    ],
]);

$schema = new Schema([
    'query' => $queryType,
    'mutation' => $mutationType,
]);


/** -------------------------------------------------
 * Execute request
 * ------------------------------------------------- */
$input = json_decode(file_get_contents('php://input'), true);

$query = $input['query'] ?? '';
$variables = $input['variables'] ?? [];

try {
    $result = GraphQL::executeQuery(
        $schema,
        $query,
        null,
        null,
        $variables
    );

    echo json_encode($result->toArray());
} catch (Throwable $e) {
    error_log($e->getMessage());
    error_log($e->getTraceAsString());

    http_response_code(500);
    echo json_encode([
        'errors' => [
            ['message' => 'Internal server error'],
        ],
    ]);
}
