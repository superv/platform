<?php

namespace SuperV\Platform\Contracts;

interface Repository
{
    public function uuid(): string;

    public function all(): array;

    public function set($key, $value = null);

    public function push($key, $value);

    public function has($key): bool;

    public function save();

    public function get($key);

    public static function load(string $uuid);
}