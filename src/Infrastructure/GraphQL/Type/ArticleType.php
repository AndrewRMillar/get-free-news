<?php

declare(strict_types=1);

namespace Infrastructure\GraphQL\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class ArticleType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
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
    }
}
