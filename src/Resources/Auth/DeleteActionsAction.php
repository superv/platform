<?php

namespace SuperV\Platform\Resources\Auth;

use SuperV\Platform\Domains\Resource\Table\Actions\SelectionAction;

class DeleteActionsAction extends SelectionAction
{
    protected $name = 'delete_selected_actions';

    protected $title = 'Delete Selected';

    /** @param \Illuminate\Database\Eloquent\Builder $query */
    public function handle($query)
    {
        $count = $query->count();

        $query->get()->map(function ($item) {
            $item->delete();
        });

        return ['data' => ['message' => sprintf("%s items were deleted", $count)]];
    }
}