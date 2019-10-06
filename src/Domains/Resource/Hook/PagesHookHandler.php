<?php

namespace SuperV\Platform\Domains\Resource\Hook;

use SuperV\Platform\Domains\Resource\Hook\Contracts\PageRenderedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\PageResolvedHook;

class PagesHookHandler extends HookHandler
{
    protected $map = [
        'resolved' => PageResolvedHook::class,
        'rendered' => PageRenderedHook::class,
    ];

    protected $hookType = 'pages';
}
