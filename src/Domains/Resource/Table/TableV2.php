<?php

namespace SuperV\Platform\Domains\Resource\Table;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesUIComponent;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\Table\Contracts\DataProvider;
use SuperV\Platform\Domains\UI\Components\Component;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Composable;
use SuperV\Platform\Support\Composer\Composition;
use SuperV\Platform\Support\Composer\Tokens;
use SuperV\Platform\Support\Concerns\HasOptions;

class TableV2 implements Composable, ProvidesUIComponent, Responsable
{
    use HasOptions;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

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
        $this->provider->setQuery($this->resource->newQuery());
        $this->provider->setRowsPerPage($this->getOption('limit', 10));
        $this->provider->fetch();

        $this->pagination = $this->provider->getPagination();

        $this->rows = $this->provider->getEntries()->map(
            function (EntryContract $entry) {
                return [
                    'fields' => ResourceFactory::make($entry)
                                               ->getTableFields()
                                               ->map(function (Field $field) use ($entry) {
                                                   return (new FieldComposer($field))->forTableRow($entry);
                                               })->values(),
                ];
            });

        return $this;
    }

    public function compose(Tokens $tokens = null)
    {
        return [
            'rows'       => $this->getRows(),
            'pagination' => $this->getPagination(),
        ];
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

    public function setResource(\SuperV\Platform\Domains\Resource\Resource $resource): TableV2
    {
        $this->resource = $resource;

        return $this;
    }

    public function makeComponent(): ComponentContract
    {
        return Component::make('sv-table')->setProps($this->composeConfig());
    }

    public function composeConfig()
    {
        $composition = new Composition([
            'config' => [
                'data_url' => sv_url($this->resource->route('index.table').'/data'),
                'fields'   => $this->resource->getTableFields()
                                             ->map(function (Field $field) {
                                                 return (new FieldComposer($field))->forTableConfig();
                                             })
                                             ->values(),

            ],
        ]);

        return $composition->get();
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