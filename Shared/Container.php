<?php

declare(strict_types=1);

namespace Shared;

use RuntimeException;

final class Container
{
    /** @var array<string, callable> */
    private array $factories = [];

    /** @var array<string, mixed> */
    private array $instances = [];

    public function set(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
    }

    public function get(string $id): mixed
    {
        // Return existing instance (singleton)
        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }

        if (!isset($this->factories[$id])) {
            throw new RuntimeException("Service '{$id}' not found in container.");
        }

        // Build and cache instance
        $this->instances[$id] = ($this->factories[$id])($this);

        return $this->instances[$id];
    }
}
