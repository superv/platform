<?php

namespace SuperV\Platform\Domains\Resource\Hook;

use SuperV\Platform\Domains\Resource\Hook\Contracts\FormResolvedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\FormResolvingHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\FormValidatingHook;

class FormsHookHandler extends HookHandler
{
    protected $map = [
        'resolving'  => FormResolvingHook::class,
        'resolved'   => FormResolvedHook::class,
        'validating' => FormValidatingHook::class,
    ];

    protected $hookType = 'forms';
}
