<?php

namespace SuperV\Platform\Domains\Auth\Access\Guard;

interface HasGuardableItems
{
    public function getGuardableItems();

    public function setGuardableItems($items);
}