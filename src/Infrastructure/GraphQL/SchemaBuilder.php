<?php

declare(strict_types=1);

namespace Infrastructure\GraphQL;

use Application\ArticleService;
use Application\ArticleException;
use Application\HomepageLinkExtractor;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Error\UserError;
use Infrastructure\GraphQL\Type\ArticleType;
use Infrastructure\GraphQL\Type\LinkType;
use Infrastructure\ArticleRepository;
use Infrastructure\HttpFetcher;
use Throwable;

class SchemaBuilder
{
    public function __construct(
        private ArticleType $articleType,
        private ArticleRepository $articleRepository,
        private ArticleService $articleService,
        private LinkType $homepageLinkType,
        private HttpFetcher $fetcher,
        private HomepageLinkExtractor $homepageLinkExtractor
    ) {
        // No initialization needed for now
    }

    public function getQueryType(): ObjectType
    {
        $fetcher = $this->fetcher;
        $homepageLinkExtractor = $this->homepageLinkExtractor;

        return new ObjectType([
            'name' => 'Query',
            'fields' => [
                'article' => [
                    'type' => $this->articleType,
                    'args' => [
                        'id' => Type::nonNull(Type::int()),
                    ],
                    'resolve' => fn($root, array $args) =>
                    $this->articleRepository->findById($args['id']),
                ],

                'articles' => [
                    'type' => Type::listOf($this->articleType),
                    'resolve' => fn() => $this->articleRepository->findAll(),
                ],

                'homepageLinks' => [
                    'type' => Type::listOf($this->homepageLinkType),
                    'args' => [
                        'limit' => [
                            'type' => Type::int(),
                            'defaultValue' => 50,
                        ],
                    ],
                    'resolve' => function ($root, $args) {
                        $html = $this->fetcher->fetch(HomepageLinkExtractor::HOMEPAGE_URL);

                        if (!$html) {
                            throw new UserError('Kon homepage niet laden');
                        }

                        return $this->homepageLinkExtractor->extract(
                            $html,
                            $args['limit']
                        );
                    },
                ],
            ],
        ]);
    }

    public function getMutationType(): ObjectType
    {
        $articleService = $this->articleService;
        $articleRepository = $this->articleRepository;

        return new ObjectType([
            'name' => 'Mutation',
            'fields' => [
                'fetchArticle' => [
                    'type' => $this->articleType,
                    'args' => [
                        'url' => Type::nonNull(Type::string()),
                    ],
                    'resolve' => function ($root, array $args) {
                        try {
                            $content = $this->articleService->fetchAndSave($args['url']);

                            if (!$content) {
                                throw new ArticleException('Could not read article.');
                            }

                            return $this->articleRepository->findByUrl($args['url']);
                        } catch (ArticleException $e) {
                            throw new UserError(
                                $e->getMessage(),
                                0,
                                null,
                            );
                        } catch (Throwable $e) {
                            error_log($e->getMessage());
                            error_log($e->getTraceAsString());

                            throw new UserError(
                                'An error occurred while processing the article.',
                                0,
                            );
                        }
                    },
                ],
            ],
        ]);
    }
}
