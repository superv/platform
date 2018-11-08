<?php

namespace SuperV\Platform\Domains\Resource\Table;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SuperV\Platform\Domains\Resource\Action\Action;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesQuery;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Exceptions\PlatformException;

class TableConfig
{
    public $query;

    protected $uuid;

    /**
     * Table title
     * @var string
     */
    protected $title;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $columns;

    /** @var Collection */
    protected $actions;

    /** @var Resource */
    protected $resource;

    protected $url;

    protected $built = false;

    /** @var ProvidesQuery */
    protected $queryProvider;

    public function build(): self
    {
        $this->validate();

        $this->uuid = Str::uuid();

        $this->columns = $this->resource->getFields()
                                        ->map(function (FieldType $field) {
                                            if ($field->getConfigValue('hide.table') === true) {
                                                return null;
                                            }

                                            return $field;
                                        })
                                        ->filter();

        $this->actions = $this->actions ? collect($this->actions) : collect([Action::make('edit'),
            Action::make('delete')]);

        // build Url
        $this->url = sv_url($this->resource->route('table', ['uuid' => $this->uuid]));

        $this->built = true;

        $this->cache();

        return $this;
    }

    public function newQuery()
    {
        if ($this->queryProvider) {
            return $this->queryProvider->newQuery();
        }

        return $this->query ?: $this->resource->newEntryInstance()->newQuery()->select($this->resource->slug().'.*');
    }

    public function compose()
    {
        if (! $this->isBuilt()) {
            throw new PlatformException('Table Config is not built yet');
        }

        return [
            'config' => [
                'meta' => [
                    'columns' => $this->getColumns()
                                      ->map(function (FieldType $field) {
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

    public function removeColumn(string $name)
    {
        $this->columns = $this->columns->filter(function(FieldType $field) use ($name) {
            return $field->getName() !== $name;
        });
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

    public function queryProvider(ProvidesQuery $queryProvider): TableConfig
    {
        $this->queryProvider = $queryProvider;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
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