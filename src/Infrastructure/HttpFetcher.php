<?php

declare(strict_types=1);

namespace Infrastructure;

final class HttpFetcher
{
    public function fetch(string $url): string|false
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; Googlebot/2.1)',
            CURLOPT_REFERER => 'https://www.google.com',
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $result = curl_exec($ch);
        $success = curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;

        if ($result === true || ($result && strlen($result) < 100) || !$success) {
            return false;
        }

        return $result;
    }
}
