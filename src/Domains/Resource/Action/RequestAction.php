<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Resource\Contracts\HandlesRequests;

abstract class RequestAction extends Action implements HandlesRequests
{
    protected $requestUrl;

    public function getRequestUrl()
    {
        return $this->requestUrl;
    }

    public function setRequestUrl($requestUrl): void
    {
        $this->requestUrl = $requestUrl;
    }
}