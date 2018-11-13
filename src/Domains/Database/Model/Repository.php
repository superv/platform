<?php

namespace SuperV\Platform\Domains\Database\Model;

interface Repository
{
    public function make($entry, $owner);

    public function resolve($entry, $owner);
}