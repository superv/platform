<?php

namespace SuperV\Platform\Domains\Resource\Table;

use Event;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Field\FieldQuerySorter;
use SuperV\Platform\Domains\Resource\Resource;

class ResourceTable extends EntryTable
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    public function getFields()
    {
        return $this->resource->indexFields()->get();
    }

    public function composeConfig()
    {
        Event::fire($this->getIdentifier().'.events:config', [
            'table'    => $this,
            'fields'   => $this->resource->indexFields(),
            'resource' => $this->resource,
        ]);

        return parent::composeConfig();
    }

    public function build()
    {
        Event::fire($this->getIdentifier().'.events:config', [
            'table'    => $this,
            'fields'   => $this->resource->indexFields(),
            'resource' => $this->resource,
        ]);

        $return = parent::build();

        Event::fire($this->getIdentifier().'.events:data', ['table' => $this, 'rows' => $this->rows]);

        return $return;
    }

    public function getFilters(): Collection
    {
        return $this->resource->getFilters();
    }

    public function getQuery()
    {
        if (! $this->query) {
            $this->query = $this->resource->newQuery();
        }

        return $this->query;
    }

    /** @param \Illuminate\Database\Eloquent\Builder $query */
    protected function applyOptions($query)
    {
        if ($this->request && $orderBy = $this->request->get('order_by')) {
            [$column, $direction] = explode(':', $orderBy);

            $field = $this->resource->getField($column);

            $sorter = app(FieldQuerySorter::class);
            $sorter->setField($field);
            $sorter->setQuery($query);
            $sorter->sort($direction);
        } elseif ($orderBy = $this->options->get('order_by')) {
            if (is_string($orderBy)) {
                $orderBy = [$orderBy => 'ASC'];
            }
            foreach ($orderBy as $column => $direction) {
                $query->orderBy($query->getModel()->getTable().'.'.$column, $direction);
            }
        } elseif ($field = $this->resource->fields()->getEntryLabelField()) {
            $query->orderBy(
                $field->getColumnName(), 'ASC'
            );
        } else {
            parent::applyOptions($query);
        }
    }

    public function getDataUrl()
    {
        if ($this->dataUrl) {
            return $this->dataUrl;
        }

        return $this->resource->route('table').'/data';
    }

    protected function getRowKeyName()
    {
        return $this->resource->config()->getKeyName() ?? 'id';
    }

    protected function getRowId($rowEntry)
    {
        return $rowEntry->getAttribute($this->getRowKeyName());
    }

    public function onQuerying($query)
    {
        if ($this->resource->isRestorable()) {
            $query->where('deleted_at', null);
        }
        $this->resource->fire('table.querying', ['query' => $query]);
    }

    public function setResource(Resource $resource): ResourceTable
    {
        $this->resource = $resource;

        return $this;
    }
}
