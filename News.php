<?php

declare(strict_types=1);

// TODO: make class single responsibility principle compliant
class News
{
    public string $title;
    public string $content;
    public string $url;
    public array $articles = [];

    public function __construct(
        string $url = '',
        string $title = '',
        string $content = ''
    ) {
        $this->title = $title;
        $this->content = $content;
        $this->url = $url;
    }


    /**
     * Get HTML using cURL
     */
    public function fetchUrl(string $url): string|false
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            CURLOPT_REFERER => 'https://www.google.com',
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $html = curl_exec($ch);

        return $html ?: false;
    }

    /**
     * Make relative URLs absolute
     */
    private function makeAbsoluteUrl(string $base, string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '//')) {
            return 'https:' . $path;
        }

        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Extract artivcle content from HTML
     */
    public function extractReadableContent(string $html, string $url): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        $xpath = new DOMXPath($dom);

        // Base URL
        $parsed = parse_url($url);
        $baseUrl = $parsed['scheme'] . '://' . $parsed['host'];

        // Main image
        $entries = $xpath->query('//link[@rel="preload" and @as="image"]', $dom);

        $imageSrcSet = '';
        $imageSizes = '';

        foreach ($entries as $entry) {
            if (!$entry instanceof DOMElement) {
                continue;
            }

            $imageSrcSet = $entry->getAttribute('imagesrcset');
            $imageSizes = $entry->getAttribute('imagesizes');
        }


        $main = $xpath->query('//main[@id="main"]')->item(0);
        if (!$main) {
            return '<p><em>Geen main-content gevonden.</em></p>';
        }

        $article = $xpath->query('.//article', $main)->item(0);
        if (!$article) {
            return '<p><em>Geen artikel gevonden.</em></p>';
        }

        $header = $xpath->query('./header', $article)->item(0);

        $output = '<article>';

        if ($header) {

            $titleNode = $xpath->query('.//h1', $header)->item(0);
            $title = htmlspecialchars(trim($titleNode->textContent));
            if ($titleNode) {
                $output .= '<h1>' . $title . '</h1>';
                $output .= "<img srcset=\"{$imageSrcSet}\" sizes=\"{$imageSizes}\" alt=\"{$title}\" class=\"w-full\"/>";
            }

            $timeNode = $xpath->query('.//time[@datetime]', $header)->item(0);
            if ($timeNode) {
                $dateText = trim($timeNode->textContent);
                $output .= '<p style="color:#555;font-size:14px;margin-top:-10px;">'
                    . htmlspecialchars($dateText)
                    . '</p>';
            }
        }

        $section = $xpath->query('./section', $article)->item(0);
        if (!$section) {
            return $output . '<p><em>Geen artikel-body gevonden.</em></p></article>';
        }

        // Remove unwanted elements
        foreach ($xpath->query('.//aside | .//button | .//*[@aria-hidden="true"] | .//*[@title="Aanmelden voor nieuwsbrief"]', $section) as $remove) {
            $remove->parentNode?->removeChild($remove);
        }

        // Links
        foreach ($xpath->query('.//a', $section) as $a) {
            if (!$a instanceof DOMElement) {
                continue;
            }

            $href = $a->getAttribute('href');
            if ($href && !str_starts_with($href, 'http')) {
                $a->setAttribute('href', $this->makeAbsoluteUrl($baseUrl, $href));
            }
        }

        // Images + lazy loading (seem to be missing from curl document)
        foreach ($xpath->query('.//img', $section) as $img) {

            if (!$img instanceof DOMElement) {
                continue;
            }

            // Lazy loading
            if ($img->getAttribute('src') === '') {

                if ($img->hasAttribute('data-src')) {
                    $img->setAttribute('src', $img->getAttribute('data-src'));
                } elseif ($img->hasAttribute('data-srcset')) {
                    $src = trim(explode(' ', $img->getAttribute('data-srcset'))[0]);
                    $img->setAttribute('src', $src);
                } elseif ($img->hasAttribute('srcset')) {
                    $src = trim(explode(' ', $img->getAttribute('srcset'))[0]);
                    $img->setAttribute('src', $src);
                }
            }

            // Make src urls absolute
            $src = $img->getAttribute('src');
            if ($src && !str_starts_with($src, 'http')) {
                $img->setAttribute('src', $this->makeAbsoluteUrl($baseUrl, $src));
            }

            if ($img->hasAttribute('loading')) {
                $img->removeAttribute('loading');
            }

            $img->setAttribute('style', 'max-width:100%;height:auto;');
        }

        $output .= $dom->saveHTML($section);
        $output .= '</article>';

        $this->saveArticleToJson($title ?? 'Onbekend', $url, $output);

        return $output;
    }

    /**
     * Save article to JSON file
     */
    private function saveArticleToJson(string $title, string $url, string $content): void
    {
        $data = [
            'id' => md5((string) date('c')),
            'title' => $title,
            'url' => $url,
            'content' => $content,
            'saved_at' => date('c'),
        ];

        $filename = 'articles.json';

        if (file_exists($filename)) {

            $existingData = json_decode(file_get_contents($filename) ?: '[]', true);
            if (is_array($existingData)) {
                if ($this->isInExistingData($existingData, $data)) {
                    return;
                }
                $existingData[] = $data;
                $data = $existingData;
            }
        } else {
            $data = [$data];
        }

        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function isInExistingData(array $existingData, array $newData): bool
    {
        foreach ($existingData as $article) {
            if ($article['title'] === $newData['title']) {
                return true;
            }
        }
        return false;
    }

    public function getArticlesFromFile(): array
    {
        $filename = 'articles.json';

        if (file_exists($filename)) {
            $existingData = json_decode(file_get_contents($filename) ?: '[]', true);
            if (is_array($existingData)) {
                return $existingData;
            }
        }

        return [];
    }

    public function loadArticle($article): void
    {
        // TODO: load article from json to the $content variable
        $this->content = $article['content'];
    }
}
