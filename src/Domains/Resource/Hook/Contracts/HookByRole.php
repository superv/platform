<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

interface HookByRole
{
    public static function getRole(): string;
}