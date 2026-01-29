<?php

declare(strict_types=1);

// TODO: implement a sqlite database storage
final class ArticleRepository
{
    private const DB_PATH = __DIR__ . '/../../data/articles.db';
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = new PDO('sqlite:' . self::DB_PATH);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function save(Article $article): void
    {
        $articles = $this->findAll();

        foreach ($articles as $existing) {
            if ($existing->url === $article->url) {
                return;
            }
        }

        $articles[] = $article;

        $stmt = $this->pdo->prepare('
            INSERT INTO articles (id, title, url, content, publication_date)
            VALUES (:id, :title, :url, :content, :publication_date)
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
}
