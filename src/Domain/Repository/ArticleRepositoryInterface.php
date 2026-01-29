<?php

declare(strict_types=1);

namespace Domain\Repository;

use Domain\Model\Article;

interface ArticleRepositoryInterface
{
    public function save(Article $article): void;
    public function findAll(): array;
    public function findById(int $id): ?Article;
}
