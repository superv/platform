<?php

namespace SuperV\Platform\Domains\Resource\Table;


class ResourceTable extends Table
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    public function setResource(\SuperV\Platform\Domains\Resource\Resource $resource): ResourceTable
    {
        $this->resource = $resource;

        $this->config = $resource->provideTableConfig();

        return $this;
    }

    /**
     * @return array
     */
    protected function makeTokens(): array
    {
        return ['res' => ['handle' => $this->resource->getHandle()]];
    }
}