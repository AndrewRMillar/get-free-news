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

// Define Article type
$articleType = new ObjectType([
    'name' => 'Article',
    'fields' => [
        'id' => Type::string(),
        'title' => Type::string(),
        'content' => Type::string(),
        'url' => Type::string(),
        'savedAt' => Type::string(),
    ]
]);

// Define Query type
$queryType = new ObjectType([
    'name' => 'Query',
    'fields' => [
        'article' => [
            'type' => $articleType,
            'args' => [
                'id' => Type::nonNull(Type::string())
            ],
            'resolve' => function ($root, $args) use ($articles) {
                foreach ($articles as $article) {
                    if ($article['id'] === $args['id']) {
                        return $article;
                    }
                }
                return null;
            }
        ],
        'articles' => [
            'type' => Type::listOf($articleType),
            'resolve' => fn() => $articles
        ]
    ]
]);

$schema = new Schema([
    'query' => $queryType
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
