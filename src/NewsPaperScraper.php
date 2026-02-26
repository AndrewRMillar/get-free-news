<?php

namespace Src;

class NewsPaperScraper
{
    private const BLACKLIST = [
        "volkskrant-magazine",
        "dossier",
        "sport",
        "file://",
        "kijkverder",
        "mensen",
        "columns-van-de-dag",
        "cartoons",
        "podcasts",
        "muziek",
        "tentoonstellingen",
        "boeken",
        "puzzels"
    ];

    public function __construct(
        private array $urls,
        private string $userAgent = 'Mozilla/5.0',
        private int $maxRetries = 3,
        private string $logFile = 'scraper.log',
        private string $tempDir = '',
        private string $folderName = __DIR__ . '/scraped-pages/'
    ) {
        $this->tempDir = $this->tempDir ?: sys_get_temp_dir();
    }

    private function alreadyScrapedToday(string $filename): bool
    {
        if (!file_exists($filename)) {
            return false;
        }

        $json  = file_get_contents($filename);
        $data  = json_decode($json, true);
        $today = date('Y-m-d');

        return !empty($data[$today]);
    }

    /** Main entry point for cron or CLI */
    public function run(): void
    {
        foreach ($this->urls as $url) {
            $url = trim($url);
            if ($url === '') {
                continue;
            }

            $filename = $this->outputFileName($url);

            if ($this->alreadyScrapedToday($filename)) {
                $this->log("Vandaag al gescraped: $url");
                continue;
            }

            $this->log("Processing {$url}");

            $html = $this->tryFetchWithRetries($url);

            if (!$html) {
                $this->log("❌ Failed after {$this->maxRetries} attempts for {$url}");
                continue;
            }

            $xpath = $this->createXPath($html);
            if (!$xpath) {
                $this->log("❌ Invalid HTML structure for {$url}");
                continue;
            }

            $links = $this->extractLinks($xpath);

            if (empty($links)) {
                $this->log("⚠️ No valid links from {$url}");
                continue;
            }

            $this->save($filename, $links);
            $this->log("✅ Saved " . count($links) . " links to {$filename}");
        }
    }

    /** Try fetch a URL up to N times */
    private function tryFetchWithRetries(string $url): ?string
    {
        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            $this->log("Fetching attempt {$attempt} for {$url}");
            $html = $this->fetchHtml($url);

            // simple success criteria — you can expand this easily
            if ($html && strlen($html) > 500) {
                return $html;
            }

            sleep(2); // wait a bit before retrying
        }

        return null;
    }

    /** Build output file name from URL */
    private function outputFileName(string $url): string
    {
        if (!is_dir($this->folderName)) {
            mkdir($this->folderName, 0755);
        }

        $path = parse_url($url, PHP_URL_PATH) ?? '/';
        $base = basename(rtrim($path, '/')) ?: 'home';
        return $this->folderName . "{$base}.json";
    }

    /** Fetch a page with curl and return HTML */
    private function fetchHtml(string $url): string
    {
        $tempFile = "{$this->tempDir}/scraper_output.html";

        // use curl with exit code
        $cmd = sprintf(
            'chromium --headless --dump-dom %s > %s',
            escapeshellarg($url),
            escapeshellarg($tempFile)
        );

        $this->log('Executing command: ' . $cmd);

        shell_exec($cmd);

        $html = @file_get_contents($tempFile) ?: '';
        return trim(preg_replace(['/\s{2,}/', '/\n+/'], [' ', "\n"], $html));
    }

    private function createXPath(string $html): ?\DOMXPath
    {
        if ($html === '') {
            return null;
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        libxml_clear_errors();

        return new \DOMXPath($dom);
    }

    private function extractLinks(\DOMXPath $xpath): array
    {
        $links = [];
        $nodes = $xpath->query('//a[contains(@class,"wl-teaser--") or contains(@class,"linkbox-overlay")]')
            ?: $xpath->query('//a[contains(@class,"teaser--") or contains(@class,"linkbox-overlay")]');

        $this->log("Found " . $nodes->length . " potential links");
        foreach ($nodes as $node) {
            $this->log("Found node: " . $node->textContent);
            if (!$node instanceof \DOMElement) {
                continue;
            }

            $href = $node->getAttribute('href');
            if ($this->isValidLink($href)) {
                $links[] = $href;
            }
        }
        return array_values(array_unique($links));
    }

    private function isValidLink(string $href): bool
    {
        if (strlen($href) < 100) {
            return false;
        }

        foreach (self::BLACKLIST as $word) {
            if (str_contains($href, $word)) {
                return false;
            }
        }

        return true;
    }

    private function save(string $filename, array $links): void
    {
        $today = date('Y-m-d');
        $existing = file_exists($filename)
            ? json_decode(file_get_contents($filename), true) ?? []
            : [];

        $existing[$today] = isset($existing[$today])
            ? array_values(array_unique(array_merge($existing[$today], $links)))
            : $links;

        file_put_contents(
            $filename,
            json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /** Keeping it simple */
    private function log(string $message): void
    {
        $time = date('[Y-m-d H:i:s]');
        file_put_contents($this->logFile, "{$time} {$message}\n", FILE_APPEND);
    }
}
