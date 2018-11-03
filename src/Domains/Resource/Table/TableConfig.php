<?php

namespace SuperV\Platform\Domains\Resource\Table;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Exceptions\PlatformException;

class TableConfig
{
    protected $uuid;

    /**
     * @var \SuperV\Platform\Domains\Resource\Table\TableColumns
     */
    protected $columns;

    /** @var Collection */
    protected $actions;

    /** @var Resource */
    protected $resource;

    protected $url;

    protected $built = false;

    public function build(): self
    {
        $this->uuid = Str::uuid();

        // build Url
        $this->url = sv_url($this->resource->route('table.data', ['uuid' => $this->uuid]));

        $this->cache();

        $this->built = true;

        return $this;
    }

    public function compose()
    {
        if (! $this->isBuilt()) {
            throw new PlatformException('Table Config is not built yet');
        }

        return [
            'config' => [
                'meta'    => [
                    'columns' => $this->getColumns()
                                      ->map(function (Field $field) {
                                          return ['label' => $field->getLabel(), 'name' => $field->getName()];
                                      })
                                      ->all(),
                ],
                'dataUrl' => $this->getUrl(),
            ],
        ];
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function uuid()
    {
        return $this->uuid;
    }

    public function cache()
    {
        cache()->forever($this->cacheKey(), serialize($this));
    }

    public function cacheKey(): string
    {
        return 'sv:tables:'.$this->uuid();
    }

    public function getColumns(): Collection
    {
        return $this->columns;
    }

    public function setColumns(TableColumns $columns): TableConfig
    {
        $this->columns = $columns;

        return $this;
    }

    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function setActions($actions): TableConfig
    {
        $this->actions = collect($actions);

        return $this;
    }

    public function isBuilt(): bool
    {
        return $this->built;
    }

    public function getResource(): Resource
    {
        return $this->resource;
    }

    public function setResource(Resource $resource): TableConfig
    {
        $this->resource = $resource;

        return $this;
    }

    public static function fromCache($uuid): ?TableConfig
    {
        if ($config = cache('sv:tables:'.$uuid)) {
            return unserialize($config);
        }

        return null;
    }
}