<?php

declare(strict_types=1);

final class ArticleContentExtractor
{
    public function extract(string $html, string $url): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        $xpath = new DOMXPath($dom);

        $parsed = parse_url($url);
        $baseUrl = $parsed['scheme'] . '://' . $parsed['host'];

        $main = $xpath->query('//main[@id="main"]')->item(0);
        if (!$main) {
            return '<p><em>Geen main-content gevonden.</em></p>';
        }

        $article = $xpath->query('.//article', $main)->item(0);
        if (!$article) {
            return '<p><em>Geen artikel gevonden.</em></p>';
        }

        $section = $xpath->query('./section', $article)->item(0);
        if (!$section) {
            return '<p><em>Geen artikel-body gevonden.</em></p>';
        }

        // Clean up unwanted elements
        foreach ($xpath->query('.//aside | .//button | .//*[@aria-hidden="true"]', $section) as $remove) {
            $remove->parentNode?->removeChild($remove);
        }

        // Fix relative links
        foreach ($xpath->query('.//a', $section) as $a) {
            if (!$a instanceof DOMElement) {
                continue;
            }

            $href = $a->getAttribute('href');
            if ($href && !str_starts_with($href, 'http')) {
                $a->setAttribute(
                    'href',
                    rtrim($baseUrl, '/') . '/' . ltrim($href, '/')
                );
            }
        }

        return '<article>' . $dom->saveHTML($section) . '</article>';
    }
}
