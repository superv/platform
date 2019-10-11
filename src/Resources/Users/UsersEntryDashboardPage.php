<?php

namespace SuperV\Platform\Resources\Users;

use SuperV\Platform\Domains\Resource\Hook\Contracts\PageResolvedHook;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\UI\Page\ResourcePage;

class UsersEntryDashboardPage implements PageResolvedHook
{
    public static $identifier = 'platform.users.pages:entry_dashboard';

    public function resolved(ResourcePage $page, Resource $resource)
    {
        $page->addAction('update_password');
        $page->addAction('impersonate');
    }
}
