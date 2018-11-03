<?php

namespace SuperV\Platform\Domains\Resource\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceEntryModel;
use SuperV\Platform\Domains\Resource\Table\TableColumns;
use Illuminate\Support\Collection;

class TableConfig
{
    protected $uuid;


    /**
     * @var \SuperV\Platform\Domains\Resource\Table\TableColumns
     */
    protected $columns;

    protected $url;

    public function build(): self
    {
        $this->uuid = Str::uuid();

        // build Url
        $this->url = sv_url('sv/tables/'.$this->uuid());

        $this->cache();

        return $this;
    }

    public function compose()
    {
        return [
            'url'     => $this->getUrl(),
            'columns' => $this->getColumns()
                              ->map(function (Field $field) {
                                  return ['label' => $field->getLabel(), 'name' => $field->getName()];
                              })
                              ->all(),
        ];
    }

    public function getUrl()
    {
        return $this->url;
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
}