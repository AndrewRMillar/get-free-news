<?php

require_once __DIR__ . '/VolkskrantMultiScraper.php';

$urls = [
    'https://www.volkskrant.nl',
    'https://www.volkskrant.nl/binnenland/',
    'https://www.volkskrant.nl/buitenland/',
    'https://www.volkskrant.nl/wetenschap/',
    'https://www.volkskrant.nl/opinie/'
];

$scraper = new VolkskrantMultiScraper($urls);

$scraper->run();
