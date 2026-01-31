<?php

declare(strict_types=1);

$databasePath = __DIR__ . '/../data/articles.sqlite';
$jsonPath     = __DIR__ . '/../data/articles.json';

if (!file_exists($jsonPath)) {
    die("JSON file not found: {$jsonPath}\n");
}

$pdo = new PDO('sqlite:' . $databasePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Load JSON
$data = json_decode(file_get_contents($jsonPath), true);

if (!is_array($data)) {
    die("Invalid JSON structure\n");
}

$stmt = $pdo->prepare('
    INSERT INTO articles (id, title, url, content, publication_date)
    VALUES (:id, :title, :url, :content, :publication_date)
    ON CONFLICT(id) DO NOTHING
');

$imported = 0;
$skipped  = 0;

foreach ($data as $item) {
    if (
        !isset(
            $item['id'],
            $item['title'],
            $item['url'],
            $item['content'],
            $item['published_at']
        )
    ) {
        echo "⚠️  Skipping invalid record\n";
        continue;
    }

    $stmt->execute([
        ':id'               => (int) $item['id'],
        ':title'            => $item['title'],
        ':url'              => $item['url'],
        ':content'          => $item['content'],
        ':publication_date' => $item['published_at'],
    ]);

    if ($stmt->rowCount() === 0) {
        $skipped++;
    } else {
        $imported++;
    }
}

echo "Import finished\n";
echo "Imported: {$imported}\n";
echo "Skipped (duplicates): {$skipped}\n";
