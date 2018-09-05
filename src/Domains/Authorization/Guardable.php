<?php

namespace SuperV\Platform\Domains\Authorization;

interface Guardable
{
    public function getRequiredAbility();
}