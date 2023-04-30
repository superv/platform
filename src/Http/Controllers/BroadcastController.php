<?php

namespace SuperV\Platform\Http\Controllers;

use Current;
use Illuminate\Http\Request;

class BroadcastController extends BaseApiController
{
    public function authenticate(Request $request)
    {
        sv_console(array_merge($request->all(), ['id' => Current::userId(), 'name' => Current::user()->name]));

        if (starts_with($request->get('channel_name'), 'presence-')) {
            return ['id' => Current::userId(), 'name' => Current::user()->name];
        }

        return true;
    }
}