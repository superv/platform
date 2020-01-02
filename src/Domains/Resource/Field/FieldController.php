<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Illuminate\Http\Request;

class FieldController
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}