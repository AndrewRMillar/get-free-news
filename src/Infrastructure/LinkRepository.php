<?php

declare(strict_types=1);

namespace Infrastructure;

use DateTime;
use PDO;
use Domain\Model\Link;
use Domain\Repository\LinkRepositoryInterface;

class LinkRepository implements LinkRepositoryInterface
{
    public function __construct(private PDO $pdo) {}

    public function save(Link $link): void
    {
        // Implementation for saving the link if needed
        $stmt = $this->pdo->prepare('
            INSERT INTO articles (id, date, links)
            VALUES (:id, :date, :urls)
            ON CONFLICT(id) DO NOTHING
        ');

        $stmt->execute([
            ':id' => $link->id,
            ':date' => $link->date,
            ':urls' => $link->urls,
        ]);
    }

    public function getLinksForDate(DateTime $date): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM links WHERE DATE(date) = :date
        ');

        $stmt->execute([
            ':date' => $date->format('Y-m-d'),
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
