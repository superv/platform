<?php

namespace SuperV\Platform\Domains\Authorization;

interface Haydar
{
    public function can($ability): bool;
}