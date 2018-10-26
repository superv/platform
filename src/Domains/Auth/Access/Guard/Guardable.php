<?php

namespace SuperV\Platform\Domains\Auth\Access\Guard;

interface Guardable
{
    public function guardKey(): ?string;
}