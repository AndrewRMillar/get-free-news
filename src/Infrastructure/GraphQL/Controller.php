<?php

declare(strict_types=1);

namespace Infrastructure\GraphQL;

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use Infrastructure\GraphQL\SchemaBuilder;
use Throwable;

class Controller
{
    public function __construct(
        private SchemaBuilder $schemaBuilder
    ) {}

    public function handleRequest($input): string
    {
        $query = $input['query'] ?? '';
        $variables = $input['variables'] ?? [];

        try {
            $result = GraphQL::executeQuery(
                $this->getSchema(),
                $query,
                null,
                null,
                $variables
            );

            return json_encode($result->toArray());
        } catch (Throwable $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());

            http_response_code(500);
            return json_encode([
                'errors' => [
                    ['message' => 'Internal server error'],
                ],
            ]);
        }
    }

    private function getSchema(): Schema
    {
        return new Schema([
            'query' => $this->schemaBuilder->getQueryType(),
            'mutation' => $this->schemaBuilder->getMutationType(),
        ]);
    }
}
