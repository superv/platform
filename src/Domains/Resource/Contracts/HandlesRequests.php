<?php

namespace SuperV\Platform\Domains\Resource\Contracts;

interface HandlesRequests
{
    public function handleRequest(\Illuminate\Http\Request $request);
}