<?php

namespace SuperV\Platform\Domains\Resource\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceEntryModel;
use SuperV\Platform\Support\Concerns\HasOptions;

class Table
{
    use HasOptions;

    /**
     * @var \SuperV\Platform\Domains\Resource\Table\TableConfig
     */
    protected $config;

    /** @var Resource */
    protected $resource;

    /** @var Builder */
    protected $query;

    /** @var Collection */
    protected $rows;

    /** @var array */
    protected $pagination;

    public function __construct()
    {
        $this->options = collect();
        $this->rows = collect();
    }

    public function build()
    {
        $entries = $this->fetchEntries($this->newQuery());

        $entries->transform(function (ResourceEntryModel $entry) {
            return $this->resource->fresh()->setEntry($entry);
        });

        $this->buildRows($entries);
    }

    protected function fetchEntries(Builder $query)
    {
        /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
        $paginator = $query->paginate($this->getOption('limit', 10));
        $countBefore = $paginator->getCollection()->count();
        $entries = sv_guard($paginator->getCollection());

        // Repaginate if guard filtered some of the entries..
        // Not ideal but should do the trick for now
        if ($countBefore !== $entries->count()) {
            $paginator = new LengthAwarePaginator(
                $entries,
                $paginator->total() - ($countBefore - $entries->count()),
                $paginator->perPage(),
                $paginator->currentPage()
            );
        }

        $this->pagination = $paginator->toArray();

        unset($this->pagination['data']);

        return $entries;
    }

    protected function buildRows(Collection $entries)
    {
        $entries->map(function ($entry) {
            $row = new TableRow($this, $entry);
            $this->rows->push($row->build());
        });
    }

    public function getRows(): Collection
    {
        return $this->rows;
    }

    public function newQuery()
    {
        return $this->resource->resolveModel()->newQuery();
    }

    public function setResource(Resource $resource): Table
    {
        $this->resource = $resource;

        return $this;
    }

    public function setConfig(TableConfig $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function getColumns(): Collection
    {
        return $this->config->getColumns();
    }
}