<?php

namespace SuperV\Platform\Domains\Database\Model;

interface Morphable
{
    public function getOwnerType();

//    public function setOwnerType(string $type);

    public function getOwnerId();

//    public function setOwnerId($id);
}