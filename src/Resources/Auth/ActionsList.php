<?php

namespace SuperV\Platform\Resources\Auth;

use SuperV\Platform\Domains\Resource\Hook\Contracts\ListResolvedHook;
use SuperV\Platform\Domains\Resource\Resource\IndexFields;
use SuperV\Platform\Domains\Resource\Table\Contracts\TableInterface;

class ActionsList implements ListResolvedHook
{
    public static $identifier = 'platform.auth_actions.lists:default';

    public function resolved(TableInterface $table, IndexFields $fields)
    {
        $table->makeSelectable();

        $table->addSelectionAction(DeleteActionsAction::make('platform.auth_actions.actions:bulk'));
    }
}