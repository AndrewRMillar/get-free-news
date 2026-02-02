<?php

declare(strict_types=1);

namespace Config;

final class Paths
{
    public const ROOT = __DIR__ . '/../../';

    public const DATA = self::ROOT . 'data/';
    public const LOGS = self::ROOT . 'logs/';

    public const SQLITE_DB = self::DATA . 'articles.sqlite';
    public const DEBUG_LOG = self::LOGS . 'debug.log';
}
