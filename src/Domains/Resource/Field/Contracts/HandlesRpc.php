<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

interface HandlesRpc
{
    public function getRpcResult(array $params, array $request = []);
}