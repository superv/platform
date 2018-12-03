<?php

namespace SuperV\Platform\Domains\Resource\Table;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesQuery;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Filter\ApplyFilters;
use SuperV\Platform\Domains\Resource\Table\Contracts\DataProvider;
use SuperV\Platform\Domains\Resource\Table\Contracts\EntryTable as EntryTableContract;
use SuperV\Platform\Support\Composer\Tokens;
use SuperV\Platform\Support\Concerns\FiresCallbacks;
use SuperV\Platform\Support\Concerns\HasOptions;

class EntryTable extends Table implements EntryTableContract
{
    use HasOptions;
    use FiresCallbacks;

    protected $query;

    /** @var \SuperV\Platform\Domains\Resource\Table\Contracts\DataProvider */
    protected $provider;

    /** @var \Illuminate\Support\Collection */
    protected $filters;

    protected $pagination = [];

    protected $request;

    public function __construct(DataProvider $provider)
    {
        $this->provider = $provider;
        $this->options = collect();
    }

    public function build()
    {
        $fields = $this->makeFields();

        $query = $this->newQuery();
        $this->fire('querying', ['query' => $query]);
        $this->applyFilters($query);

        $this->provider->setQuery($query);
        $this->provider->setRowsPerPage($this->getOption('limit', 10));
        $this->provider->fetch();
        $this->pagination = $this->provider->getPagination();
        $this->rows = $this->provider->getEntries();

        $this->rows = $this->buildRows($fields);

        return $this;
    }

    protected function buildRows(Collection $fields): Collection
    {
        return $this->rows->map(
            function (EntryContract $entry) use ($fields) {
                return [
                    'id'      => $entry->getId(),
                    'fields'  => $fields->map(function (Field $field) use ($entry) {
                        return (new FieldComposer($field))->forTableRow($entry);
                    })->values(),
                    'actions' => ['view'],
                ];
            });
    }

    public function makeFields(): Collection
    {
        $fields = parent::makeFields();

        return $fields->map(function (Field $field) {
            if ($callback = $field->getCallback('table.querying')) {
                $this->on('querying', $callback);
            }

            return $field;
        });
    }

    public function compose(Tokens $tokens = null)
    {
        return [
            'rows'       => $this->getRows(),
            'pagination' => $this->getPagination(),
        ];
    }

    protected function applyFilters($query)
    {
        ApplyFilters::dispatch($this->getFilters(), $query, $this->getRequest());
    }

    protected function newQuery()
    {
        $query = $this->getQuery();

        if ($query instanceof ProvidesQuery) {
            return $query->newQuery();
        }

        return $query;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function setQuery($query): EntryTableContract
    {
        $this->query = $query;

        return $this;
    }

    public function getFilters(): Collection
    {
        return $this->filters;
    }

    public function setFilters($filters): EntryTableContract
    {
        $this->filters = wrap_collect($filters);

        return $this;
    }

    protected function getRequest()
    {
        return $this->request;
    }

    public function setRequest($request): EntryTableContract
    {
        $this->request = $request;

        return $this;
    }

    public function getPagination(): array
    {
        return $this->pagination;
    }
}