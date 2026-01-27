<?php

declare(strict_types=1);

final class Article
{
    public function __construct(
        public string $id,
        public string $title,
        public string $url,
        public string $content,
        public string $savedAt
    ) {}
}
