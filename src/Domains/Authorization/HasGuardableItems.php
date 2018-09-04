<?php

namespace SuperV\Platform\Domains\Authorization;

interface HasGuardableItems
{
    public function getGuardableItems();

    public function setGuardableItems($items);
}