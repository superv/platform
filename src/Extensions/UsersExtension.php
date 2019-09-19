<?php

namespace SuperV\Platform\Extensions;

use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsResource;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\Table\ResourceTable;
use SuperV\Platform\Domains\UI\Page\EntryPage;

class UsersExtension implements ExtendsResource
{
    public function extend(Resource $resource)
    {
        $resource->searchable(['email']);
        $resource->registerAction('update_password', UpdatePasswordAction::class);

        $resource->onIndexConfig(function (ResourceTable $table) {
        });

        $resource->onEntryDashboard(function (EntryPage $page) {
            $page->addAction('update_password');
        });

        $fields = $resource->indexFields();
        $fields->show('email');

    }

    public function extends(): string
    {
        return 'platform.users';
    }
}
