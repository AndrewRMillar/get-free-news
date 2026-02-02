<?php

namespace Domain\Model;

final class Link
{
    public function __construct(
        public int $id,
        public string $date,
        public string $urls,
    ) {}
}
