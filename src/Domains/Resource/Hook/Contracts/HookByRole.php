<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

/**
 * Interface HookByRole
 *
 * @package SuperV\Platform\Domains\Resource\Hook\Contracts
 * @see     \Tests\Platform\Domains\Resource\Hook\HookByRoleTest
 */
interface HookByRole
{
    public static function getRole(): string;
}