<?php

declare(strict_types=1);

final class ArticleRepository
{
    public function __construct(
        private string $file
    ) {}

    public function save(Article $article): void
    {
        $articles = $this->findAll();

        foreach ($articles as $existing) {
            if ($existing->title === $article->title) {
                return;
            }
        }

        $articles[] = $article;

        file_put_contents(
            $this->file,
            json_encode(
                array_map(fn($a) => [
                    'id' => $a->id,
                    'title' => $a->title,
                    'url' => $a->url,
                    'content' => $a->content,
                    'saved_at' => $a->savedAt,
                ], $articles),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            )
        );
    }

    public function findAll(): array
    {
        if (!file_exists($this->file)) {
            return [];
        }

        return array_map(
            fn($a) => new Article(
                $a['id'],
                $a['title'],
                $a['url'],
                $a['content'],
                $a['saved_at']
            ),
            json_decode(file_get_contents($this->file), true) ?? []
        );
    }
}
