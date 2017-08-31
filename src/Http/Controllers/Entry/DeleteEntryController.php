<?php

namespace SuperV\Platform\Http\Controllers\Entry;

use SuperV\Platform\Http\Controllers\BasePlatformController;

class DeleteEntryController extends BasePlatformController
{
    public function index($ticket)
    {
        if ($config = app('cache')->get('superv::entry.tickets.delete:'.$ticket)) {
            $model = array_get($config, 'model');
            $id = array_get($config, 'id');

            $entry = $model::findOrFail($id);

            $entry->delete();

            return $this->redirect->back()->withSuccess('Entry deleted');
        }

        throw new \Exception('Ticket not found: '.$ticket);
    }
}
