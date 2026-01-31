<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
$container = require __DIR__ . '/container.php';

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Error\UserError;
use Application\ArticleService;
use Application\ArticleException;
use Infrastructure\ArticleRepository;
use Infrastructure\HttpFetcher;
use Application\ArticleContentExtractor;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

$headers = getallheaders();
$csrfHeader = $headers['X-CSRF-Token'] ?? '';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    (!isset($_SESSION['csrf_token']) || $csrfHeader !== $_SESSION['csrf_token'])
) {
    http_response_code(403);
    echo json_encode([
        'errors' => [['message' => 'Invalid CSRF token']]
    ]);
    exit;
}

$pdo = new PDO(
    'sqlite:' . __DIR__ . '/../data/articles.sqlite',
    null,
    null,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

// Infrastructure & Application services

/** @var ArticleRepository $repository */
$repository = $container[ArticleRepository::class];
/** @var HttpFetcher $fetcher */
$fetcher = $container[HttpFetcher::class];
/** @var ArticleContentExtractor $extractor */
$extractor = $container[ArticleContentExtractor::class];
/** @var ArticleService $articleService */
$articleService = $container[ArticleService::class];

// GraphQL Types
$articleType = new ObjectType([
    'name' => 'Article',
    'fields' => [
        'id' => Type::int(),
        'title' => Type::string(),
        'url' => Type::string(),
        'content' => Type::string(),
        'publishedAt' => [
            'type' => Type::string(),
            'resolve' => fn($article) => $article->publishedAt
        ]
    ]
]);

// File-based storage
function loadArticles(): array
{
    $file = __DIR__ . '/../data/articles.json';

    if (!file_exists($file)) {
        return [];
    }

    return json_decode(file_get_contents($file), true) ?? [];
}

function getArticleById(int $id): ?array
{
    foreach (loadArticles() as $article) {
        if ((int)$article['id'] === $id) {
            return $article;
        }
    }

    return null;
}

// Define Query type
$queryType = new ObjectType([
    'name' => 'Query',
    'fields' => [
        'article' => [
            'type' => $articleType,
            'args' => [
                'id' => Type::nonNull(Type::int())
            ],
            'resolve' => function ($root, array $args) {
                return getArticleById($args['id']);
            },
        ],

        // List (ID + title requested by client)
        'articles' => [
            'type' => Type::listOf($articleType),
            'resolve' => fn() => $repository->findAll()
        ],
    ]
]);

$mutationType = new ObjectType([
    'name' => 'Mutation',
    'fields' => [
        'fetchArticle' => [
            'type' => $articleType,
            'args' => [
                'url' => Type::nonNull(Type::string())
            ],
            'resolve' => function ($root, $args) use ($articleService, $repository) {

                try {
                    $content = $articleService->fetchAndSave($args['url']);
                    if ($content === false) {
                        return null;
                    }

                    if (!$content) {
                        throw new ArticleException('Could not read article.');
                    }

                    // Return last saved article
                    $articles = $repository->findAll();
                    return end($articles) ?: null;
                } catch (ArticleException $e) {
                    throw new UserError($e->getMessage(), 0, null, null, null, [
                        'code' => $e->codeKey
                    ]);
                } catch (Throwable $e) {
                    // Log the real error
                    $this->logger->error($e->getMessage(), ['exception' => $e]);

                    // Send safe message to frontend
                    throw new UserError(
                        'An error occurred while processing the article.',
                        0,
                        null,
                        null,
                        null,
                        ['code' => 'INTERNAL_ERROR']
                    );
                }
            }
        ]

    ]
]);

$schema = new Schema([
    'query' => $queryType,
    'mutation' => $mutationType
]);

// Read request
$input = json_decode(file_get_contents('php://input'), true);
$query = $input['query'] ?? '';
$variables = $input['variables'] ?? [];

try {
    $result = GraphQL::executeQuery($schema, $query, null, null, $variables);
    $output = $result->toArray();
} catch (Throwable $e) {
    http_response_code(500);
    $output = [
        'errors' => [
            ['message' => $e->getMessage()]
        ]
    ];
}

echo json_encode($output);
