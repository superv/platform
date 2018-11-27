<?php

namespace SuperV\Platform\Domains\Resource\Table;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesUIComponent;
use SuperV\Platform\Domains\Resource\Table\Contracts\Column;
use SuperV\Platform\Domains\Resource\Table\Contracts\DataProvider;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Domains\UI\Components\TableComponent;
use SuperV\Platform\Support\Composer\Composable;
use SuperV\Platform\Support\Concerns\HasOptions;

class Table implements Composable, ProvidesUIComponent, Responsable
{
    use HasOptions;

    /**
     * @var TableConfig
     */
    protected $config;

    /** @var Builder */
    protected $query;

    /** @var \SuperV\Platform\Domains\Resource\Table\TableRow|\Illuminate\Support\Collection */
    protected $rows;

    /** @var \SuperV\Platform\Domains\Resource\Table\Contracts\Column[]|\Illuminate\Support\Collection */
    protected $columns;

    /** @var array */
    protected $pagination;

    /**
     * @var \SuperV\Platform\Domains\Resource\Table\Contracts\DataProvider
     */
    protected $provider;

    public function __construct(DataProvider $provider)
    {
        $this->options = collect();
        $this->rows = collect();
        $this->provider = $provider;
    }

    public function build(): self
    {
        $this->columns = $this->config->getColumns()->map(function (Column $column) {
            if ($callback = $column->getAlterQueryCallback()) {
                $callback($this->getQuery());
            }

            return $column;
        });

        $entries = $this->fetch();

        $this->rows = $this->buildRows($entries);

        return $this;
    }

    protected function buildRows(Collection $entries)
    {
        $rows = collect();
        $entries->map(
            function (EntryContract $entry) use ($rows) {
                $row = new TableRow($this, $entry);
                $rows->push($row->build());
            });

        return $rows;
    }

    /**
     * @param $query
     */
    protected function fetch(): Collection
    {
        $this->provider->setQuery($this->getQuery());
        $this->provider->setRowsPerPage($this->getOption('limit', 10));
        $this->provider->fetch();

        $this->pagination = $this->provider->getPagination();

        return $this->provider->getEntries();
    }

    public function url()
    {
        return $this->config->getDataUrl();
    }

    public function compose(\SuperV\Platform\Support\Composer\Tokens $tokens = null)
    {
        return [
            'rows'       => $this->getRows(),
            'pagination' => $this->getPagination(),
        ];
    }

    public function getConfig(): TableConfig
    {
        return $this->config;
    }

    public function setConfig(TableConfig $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function getRows(): Collection
    {
        return $this->rows;
    }

    public function getColumns(): Collection
    {
        return $this->columns;
    }

    public function getActions(): Collection
    {
        return $this->config->getRowActions();
    }

    public function getQuery()
    {
        if (! $this->query) {
            $this->query = $this->config->newQuery();
        }

        return $this->query;
    }

    public function setQuery($query): Table
    {
        $this->query = $query;

        return $this;
    }

    public function getPagination(): array
    {
        return $this->pagination;
    }

    public function makeComponent(): ComponentContract
    {
        return TableComponent::from($this);
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        return response()->json([
            'data' => sv_compose($this, $this->makeTokens()),
        ]);
    }

    /**
     * @return array
     */
    protected function makeTokens(): array
    {
        return [];
    }

    public function uuid()
    {
        return $this->config->uuid();
    }

    public static function config(TableConfig $config): self
    {
        return app(Table::class)->setConfig($config);
    }
}