<?php

namespace SuperV\Platform\Domains\Database\Model;

interface Morphable
{
    public function getOwnerType();

    public function getOwnerId();
}