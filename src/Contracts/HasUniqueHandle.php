<?php

namespace SuperV\Platform\Contracts;

interface HasUniqueHandle
{
    public function getHandle(): string;
}