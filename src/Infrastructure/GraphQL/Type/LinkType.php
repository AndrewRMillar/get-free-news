<?php

declare(strict_types=1);

namespace Infrastructure\GraphQL\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class LinkType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'HomepageLink',
            'fields' => [
                'title' => Type::string(),
                'url' => Type::string(),
            ]
        ]);
    }
}
