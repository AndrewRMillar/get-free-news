<?php

declare(strict_types=1);

namespace Infrastructure;

use Domain\Model\Article;
use Domain\Repository\ArticleRepositoryInterface;
use Psr\Log\LoggerInterface;
use PDO;

final class ArticleRepository implements ArticleRepositoryInterface
{
    public function __construct(
        private PDO $pdo,
        private LoggerInterface $logger
    ) {}

    public function save(Article $article): bool
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO articles (title, url, content, publication_date)
            VALUES (:title, :url, :content, :publication_date)
            ON CONFLICT(url) DO NOTHING
        ');

        $executed = $stmt->execute([
            ':title' => $article->title,
            ':url' => $article->url,
            ':content' => $article->content,
            ':publication_date' => $article->publishedAt,
        ]);

        // Errorcode '00000': "Execution of the operation was successful and did not result in any type of warning or exception condition."
        if ($stmt->errorCode() != '00000') {
            $this->logger->error('PDO error, error info', ['errorCode' => $stmt->errorInfo()]);
        }

        $newRows = $stmt->rowCount();

        $this->logger->info("$newRows nieuwe articelen in de database (" . __LINE__ . " " . __CLASS__ . ")", ['Articles added' => $newRows]);

        return $executed && $newRows > 0;
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
            'SELECT * FROM articles WHERE url = :url LIMIT 1'
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
