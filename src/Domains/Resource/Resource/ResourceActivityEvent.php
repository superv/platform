<?php

namespace SuperV\Platform\Domains\Resource\Resource;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class ResourceActivityEvent
{
    use Dispatchable;

    /**
     * @var \Illuminate\Http\Request
     */
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}