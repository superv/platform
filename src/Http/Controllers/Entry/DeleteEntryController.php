<?php namespace SuperV\Platform\Http\Controllers\Entry;

use SuperV\Platform\Http\Controllers\BasePlatformController;

class DeleteEntryController extends BasePlatformController
{
    public function index($ticket)
    {
        if ($config = superv('cache')->get('superv::platform.tickets:' . $ticket)) {
            $class = array_get($config, 'class');
            $id = array_get($config, 'id');

            dd($class, $id);
        }
    }
}