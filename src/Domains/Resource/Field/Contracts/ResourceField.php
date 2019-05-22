<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

interface ResourceField
{
    /**
     * @param \SuperV\Platform\Domains\Resource\Resource $resource
     */
    public function setResource(\SuperV\Platform\Domains\Resource\Resource $resource): void;

    /**
     * @return \SuperV\Platform\Domains\Resource\Resource
     */
    public function getResource(): \SuperV\Platform\Domains\Resource\Resource;
}