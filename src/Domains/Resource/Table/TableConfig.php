<?php

namespace SuperV\Platform\Domains\Resource\Table;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SuperV\Platform\Domains\Resource\Action\Action;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Exceptions\PlatformException;

class TableConfig
{
    public $query;

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
        $this->validate();

        $this->uuid = Str::uuid();

        $this->columns = $this->resource->getFields()
                                        ->map(function (Field $field) {
                                            if ($field->getConfigValue('hide.table') === true) {
                                                return null;
                                            }

                                            return $field;
                                        })
                                        ->filter();

        $this->actions = $this->actions ? collect($this->actions) : collect([Action::make('edit'), Action::make('delete')]);

        // build Url
        $this->url = sv_url($this->resource->route('table.data', ['uuid' => $this->uuid]));

        $this->built = true;

        $this->cache();

        return $this;
    }

    public function newQuery()
    {
        return $this->query ?: $this->resource->resolveModel()->newQuery()->select($this->resource->getSlug().'.*');
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

    public function getColumns(): Collection
    {
        if (! $this->isBuilt()) {
           throw new PlatformException('Config is not built yet');
        }

        return $this->columns;
    }

    public function getActions(): ?Collection
    {
        return $this->actions;
    }

    public function setActions($actions): TableConfig
    {
        $this->actions = $actions;

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

    protected function validate(): void
    {
        if ($this->isBuilt()) {
            throw new PlatformException('Config is already built');
        }

        if (! $this->resource) {
            throw new PlatformException('No resource set');
        }
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

    public static function fromCache($uuid): ?TableConfig
    {
        if ($config = cache('sv:tables:'.$uuid)) {
            $config = unserialize($config);
            return $config;
        }

        return null;
    }
}