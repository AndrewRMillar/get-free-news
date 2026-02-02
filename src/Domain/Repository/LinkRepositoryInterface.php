<?php

declare(strict_types=1);

namespace Domain\Repository;

use DateTime;
use Domain\Model\Link;

interface LinkRepositoryInterface
{
    public function save(Link $link): void;
    public function getLinksForDate(DateTime $date): array;
}
