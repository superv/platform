<?php

namespace SuperV\Platform\Domains\UI\Components;

class Component extends BaseComponent
{
    protected $name;

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

}
