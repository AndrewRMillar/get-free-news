<?php

declare(strict_types=1);

namespace Infrastructure;

use Domain\Model\Article;
use Domain\Repository\ArticleRepositoryInterface;
use PDO;

final class ArticleRepository implements ArticleRepositoryInterface
{
    public function __construct(private PDO $pdo) {}

    public function save(Article $article): bool
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO articles (id, title, url, content, publication_date)
            VALUES (:id, :title, :url, :content, :publication_date)
            ON CONFLICT(id) DO NOTHING
        ');

        $executed = $stmt->execute([
            ':id' => $article->id,
            ':title' => $article->title,
            ':url' => $article->url,
            ':content' => $article->content,
            ':publication_date' => $article->publishedAt,
        ]);

        return $executed && $stmt->rowCount() > 0;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM articles');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(
            fn($a) => new Article(
                $a['id'],
                $a['title'],
                $a['url'],
                $a['content'],
                $a['publication_date']
            ),
            $rows
        );
    }

    public function findById(int $id): ?Article
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM articles WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToArticle($row) : null;
    }

    public function findByUrl(string $url): ?Article
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM article WHERE url = :url LIMIT 1'
        );
        $stmt->execute([':url' => $url]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToArticle($row) : null;
    }

    public function findLast(): ?Article
    {
        $stmt = $this->pdo->query(
            'SELECT * FROM articles ORDER BY id DESC LIMIT 1'
        );

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToArticle($row) : null;
    }

    private function mapRowToArticle(array $row): Article
    {
        return new Article(
            (int) $row['id'],
            $row['title'],
            $row['url'],
            $row['content'],
            $row['publication_date']
        );
    }
}
