<?php

namespace SuperV\Platform\Domains\Resource\Driver;

use SuperV\Platform\Contracts\Arrayable;
use SuperV\Platform\Domains\Resource\Blueprint\Blueprint;

interface DriverInterface extends Arrayable
{
    public function toDsn(): string;

    public function getParam($key);

    public function setParam($key, $value): DriverInterface;

    public function getType();

    public function run(Blueprint $blueprint);
}