<?php

declare(strict_types=1);

namespace Domain\Model;

final class Article
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public string $content,
        public string $publishedAt,
    ) {}
}
