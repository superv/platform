<?php

namespace SuperV\Platform\Domains\Database\Model\Contracts;

interface EntryContract
{
    public function getId();

    public function getAttribute($key);

    public function setAttribute($key, $value);
}