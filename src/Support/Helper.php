<?php

declare(strict_types=1);

namespace Support;

// I'm combining helper functions into a single class for simplicity in stead of creating 
// classes like UrlHelper, DateHelper, etc.
final class Helper
{
    public static function sanitizeUrl(string $url): string
    {
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    public static function normalize(string $url): string
    {
        return rtrim($url, '/');
    }

    public static function isHttp(string $url): bool
    {
        return str_starts_with($url, 'http://')
            || str_starts_with($url, 'https://');
    }

    public static function nowIso(): string
    {
        return (new \DateTimeImmutable())->format(DATE_ATOM);
    }

    public static function stripScripts(string $html): string
    {
        return preg_replace('#<script.*?</script>#si', '', $html);
    }

    public static function normalizeWhitespace(string $html): string
    {
        return preg_replace('/\s+/', ' ', trim($html));
    }
}
