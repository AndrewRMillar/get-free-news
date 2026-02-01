<?php

declare(strict_types=1);

namespace Infrastructure;

use Domain\Model\Article;
use Domain\Repository\ArticleRepositoryInterface;
use PDO;

// TODO: implement a sqlite database storage
final class ArticleRepository implements ArticleRepositoryInterface
{
    public function __construct(private PDO $pdo) {}

    public function save(Article $article): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO articles (id, title, url, content, publication_date)
            VALUES (:id, :title, :url, :content, :publication_date)
            ON CONFLICT(id) DO NOTHING
        ');

        $stmt->execute([
            ':id' => $article->id,
            ':title' => $article->title,
            ':url' => $article->url,
            ':content' => $article->content,
            ':publication_date' => $article->publishedAt,
        ]);
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

    /* TODO: perhpas a better method wouold be to add a that checks if the article was added correctly, perhaps also?    */
    public function fetchCount(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) as count FROM articles');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $row['count'];
    }

    public function findById(int $id): ?Article
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM articles WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Article(
            (int) $row['id'],
            $row['title'],
            $row['url'],
            $row['content'],
            $row['publication_date']
        );
    }
}
