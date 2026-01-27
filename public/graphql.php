<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

$headers = getallheaders();
$csrfHeader = $headers['X-CSRF-Token'] ?? '';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && (!isset($_SESSION['csrf_token']) || $csrfHeader !== $_SESSION['csrf_token'])
) {
    http_response_code(403);
    echo json_encode([
        'errors' => [['message' => 'Invalid CSRF token']]
    ]);
    exit;
}

// Infrastructure
$repository = new ArticleRepository(
    __DIR__ . '/../data/articles.json'
);

$fetcher = new HttpFetcher();
$extractor = new ArticleContentExtractor();

// Application
$articleService = new ArticleService(
    $fetcher,
    $extractor,
    $repository
);

// GraphQL Types
$articleType = new ObjectType([
    'name' => 'Article',
    'fields' => [
        'id' => Type::int(),
        'title' => Type::string(),
        'url' => Type::string(),
        'content' => Type::string(),
        'savedAt' => [
            'type' => Type::string(),
            'resolve' => fn($article) => $article->savedAt
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

function getArticleById(string $id): ?array
{
    foreach (loadArticles() as $article) {
        if ($article['id'] === $id) {
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
            'resolve' => fn($root, $args) =>
            $repository->findAll()
                ? array_values(
                    array_filter(
                        $repository->findAll(),
                        fn($a) => $a->id === $args['id']
                    )
                )[0] ?? null : null
        ],

        // List (ID + title requested by client)
        'getArticles' => [
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

                $content = $articleService->fetchAndSave($args['url']);
                if ($content === false) {
                    return null;
                }

                // Return last saved article
                $articles = $repository->findAll();
                return end($articles) ?: null;
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
