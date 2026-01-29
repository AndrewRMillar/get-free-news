<?php

declare(strict_types=1);

namespace Application;

use RuntimeException;

final class ArticleException extends RuntimeException
{
    public function __construct(
        string $message,
        public string $codeKey = 'ARTICLE_ERROR'
    ) {
        parent::__construct($message);
    }
}
