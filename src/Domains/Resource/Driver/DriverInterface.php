<?php

namespace SuperV\Platform\Domains\Resource\Driver;

use SuperV\Platform\Contracts\Arrayable;
use SuperV\Platform\Domains\Resource\Builder\Blueprint;
use SuperV\Platform\Domains\Resource\Builder\PrimaryKey;

interface DriverInterface extends Arrayable
{
    public function toDsn(): string;

    public function getParam($key);

    public function setParam($key, $value): DriverInterface;

    public function getType();

    public function primaryKey(PrimaryKey $key): DriverInterface;

    public function getPrimaryKey(string $keyName): ?PrimaryKey;

    public function run(Blueprint $blueprint);
}