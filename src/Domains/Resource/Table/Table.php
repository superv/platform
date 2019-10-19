<?php

namespace SuperV\Platform\Domains\Resource\Table;

use Event;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Action\Action;
use SuperV\Platform\Domains\Resource\Action\DeleteEntryAction;
use SuperV\Platform\Domains\Resource\Action\EditEntryAction;
use SuperV\Platform\Domains\Resource\Action\ViewEntryAction;
use SuperV\Platform\Domains\Resource\Contracts\Filter\Filter;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesQuery;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesUIComponent;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Field\FieldQuerySorter;
use SuperV\Platform\Domains\Resource\Filter\ApplyFilters;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\Table\Contracts\TableDataProviderInterface;
use SuperV\Platform\Domains\Resource\Table\Contracts\TableInterface;
use SuperV\Platform\Domains\UI\Components\Component;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Composable;
use SuperV\Platform\Support\Composer\Tokens;
use SuperV\Platform\Support\Concerns\FiresCallbacks;
use SuperV\Platform\Support\Concerns\HasOptions;

class Table implements TableInterface, Composable, ProvidesUIComponent, Responsable
{
    use HasOptions;

    protected $identifier;

    protected $title;

    /** @var Collection */
    protected $rows;

    protected $rowActions = [];

    protected $selectionActions = [];

    protected $contextActions = [];

    protected $dataUrl;

    protected $mergeFields;

    protected $mergeFilters;

    protected $fields;

    protected $selectable = true;

    protected $deletable = true;

    protected $viewable = true;

    protected $showIdColumn = false;

    use FiresCallbacks;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    protected $query;

    /** @var \SuperV\Platform\Domains\Resource\Table\Contracts\TableDataProviderInterface */
    protected $provider;

    /** @var \Illuminate\Support\Collection */
    protected $filters;

    protected $pagination = [];

    /** @var \Illuminate\Http\Request */
    protected $request;

    protected $orderBy;

    /**
     * @var \SuperV\Platform\Contracts\Dispatcher
     */
    protected $dispatcher;

    public function __construct(TableDataProviderInterface $provider, Dispatcher $dispatcher)
    {
        $this->provider = $provider;
        $this->options = collect();
        $this->dispatcher = $dispatcher;
    }

    public function mergeFields($fields): TableInterface
    {
        $this->mergeFields = $fields;

        return $this;
    }

    public function mergeFilters($filters): TableInterface
    {
        $this->mergeFilters = $filters;

        return $this;
    }

    public function addFilter(Filter $filter): TableInterface
    {
        $this->mergeFilters[] = $filter;

        return $this;
    }

    public function addRowAction($action): TableInterface
    {
        $this->rowActions = array_merge([$action], $this->rowActions);

        return $this;
    }

    public function addSelectionAction($action): TableInterface
    {
        $this->selectionActions[] = $action;

        return $this;
    }

    public function addContextAction($action): TableInterface
    {
        $this->contextActions[] = $action;

        return $this;
    }

    public function removeRowAction($actionName)
    {
        $this->rowActions = collect($this->rowActions)->map(function ($action) use ($actionName) {
            if (is_string($action) && $action === $actionName) {
                return null;
            }

            if ($action instanceof Action && $action->getName() === $actionName) {
                return null;
            }

            return $action;
        })->filter()->all();
    }

    public function getRows(): Collection
    {
        return $this->rows;
    }

    public function setRows($rows)
    {
        $this->rows = $rows;

        return $this;
    }

    public function shouldShowIdColumn(): bool
    {
        return $this->showIdColumn;
    }

    public function setFields($fields): TableInterface
    {
        $this->fields = $fields;

        return $this;
    }

    public function makeComponent(): ComponentContract
    {
        $this->make();

        return Component::make('sv-table')->card()->setProps($this->composeConfig());
    }

    public function setRowActions($rowActions)
    {
        $this->rowActions = wrap_array($rowActions);

        return $this;
    }

    public function getAction($name)
    {
        return collect($this->getSelectionActions())->map(function ($action) {
            if (is_string($action)) {
                $action = $action::make();
            }

            return $action;
        })->first(function (Action $action) use ($name) { return $action->getName() === $name; });
    }

    public function setDataUrl($url): TableInterface
    {
        $this->dataUrl = $url;

        return $this;
    }

    public function isSelectable(): bool
    {
        return $this->selectable;
    }

    public function getRowActions()
    {
        return $this->rowActions;
    }

    public function getContextActions()
    {
        return $this->contextActions;
    }

    public function getSelectionActions()
    {
        return $this->selectionActions;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function showIdColumn()
    {
        $this->showIdColumn = true;
    }

    public function getFields()
    {
        return $this->resource->indexFields()->get();
    }

    public function makeFields(): Collection
    {
        $mergeFields = wrap_collect($this->mergeFields)
            ->map(function (FieldInterface $field) {
                return clone $field;
            });

        $fields = wrap_collect($this->getFields())->merge($mergeFields)
                                                  ->map(function ($field) {
                                                      return is_array($field) ? sv_field($field) : $field;
                                                  });

        return $fields->map(function (FieldInterface $field) {
            if ($callback = $field->getCallback('table.querying')) {
                $this->on('querying', $callback);
            }

            return $field;
        });
    }

    public function composeConfig()
    {
//        Event::dispatch($this->getIdentifier().'.events:config', [
//            'table'    => $this,
//            'fields'   => $this->resource->indexFields(),
//            'resource' => $this->resource,
//        ]);

//        $this->fireEvent('config');

        return (new TableComposer($this))->forConfig();
    }

    public function build()
    {
//        Event::dispatch($this->getIdentifier().'.events:config', [
//            'table'    => $this,
//            'fields'   => $this->resource->indexFields(),
//            'resource' => $this->resource,
//        ]);
//        $this->fireEvent('config');
        $fields = $this->makeFields();

        $query = $this->getQuery();
        if ($this->resource->isRestorable()) {
            $query->where('deleted_at', null);
        }

        $this->fireEvent('query_resolved', ['query' => $query]);
        $this->resource->fire('table.querying', ['query' => $query]);
        $this->fire('querying', ['query' => $query]);

        $this->applyFilters($query);
        $this->applyOptions($query);

        $this->provider->setQuery($query);
        $this->provider->setRowsPerPage($this->getOption('limit', 10));
        $this->provider->fetch();
        $this->pagination = $this->provider->getPagination();
        $this->rows = $this->provider->getEntries();

        $this->rows = $this->buildRows($fields);

        Event::dispatch($this->getIdentifier().'.events:data', ['table' => $this, 'rows' => $this->rows]);

        return $this;
    }

    public function getDataUrl()
    {
        if ($this->dataUrl) {
            return $this->dataUrl;
        }

        return $this->resource->route('table').'/data';
    }

    public function getFilters(): Collection
    {
        return $this->resource->getFilters()->merge($this->mergeFilters);
    }

    public function setFilters($filters): TableInterface
    {
        $this->filters = wrap_collect($filters);

        return $this;
    }

    public function getQuery()
    {
        if (! $this->query) {
            $this->query = $this->resource->newQuery()->selectRaw($this->resource->config()->getTable().'.*');
        }

        return $this->query;
    }

    public function setQuery($query): TableInterface
    {
        $this->query = $query instanceof ProvidesQuery ? $query->newQuery() : $query;

        return $this;
    }

    public function setResource(Resource $resource): TableInterface
    {
        $this->resource = $resource;

        return $this;
    }

    public function setRequest($request): TableInterface
    {
        $this->request = $request;

        return $this;
    }

    public function getPagination(): array
    {
        return $this->pagination;
    }

    public function orderByLatest()
    {
        $this->setOption('order_by', ['created_at' => 'desc']);
    }

    public function notDeletable(): TableInterface
    {
        $this->deletable = false;

        return $this;
    }

    public function notViewable(): TableInterface
    {
        $this->viewable = false;

        return $this;
    }

    public function notSelectable(): TableInterface
    {
        $this->selectable = false;

        return $this;
    }

    public function fireEvent($event, array $payload = [])
    {
        $eventName = sprintf("%s.events:%s", $this->getIdentifier(), $event);

        $payload = array_merge($payload, ['table'    => $this,
                                          'fields'   => $this->resource->indexFields(),
                                          'resource' => $this->resource]);

        $this->dispatcher->dispatch($eventName, $payload);
    }

    public function make()
    {
        if (empty($this->rowActions)) {
            if ($this->deletable) {
                $this->addRowAction(DeleteEntryAction::class);
            }

            if ($this->viewable) {
                $this->addRowAction(ViewEntryAction::class);
            }

            $this->addRowAction(EditEntryAction::class);
        } else {
        }

        return $this;
    }

    public function compose(Tokens $tokens = null)
    {
        return [
            'rows'       => $this->getRows()->all(),
            'pagination' => $this->getPagination(),
        ];
    }

    public function toResponse($request)
    {
        return response()->json([
//            'data' => sv_compose($this->compose(), $this->makeTokens()),
'data' => $this->compose(),
        ]);
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function onQuerying($on)
    {
    }

    protected function makeTokens(): array
    {
        return [];
    }

    protected function buildRows(Collection $fields): Collection
    {
        return $this->rows->map(
            function (EntryContract $entry) use ($fields) {
                return [
                    'id'      => $this->getRowId($entry),
                    'fields'  => $fields->map(function (FieldInterface $field) use ($entry) {
                        return (new FieldComposer($field))->forTableRow($entry)->get();
                    })->values()->all(),
                    'actions' => ['view'],
                ];
            });
    }

    protected function getRowKeyName()
    {
        return $this->resource->config()->getKeyName() ?? 'id';
    }

    protected function getRowId($rowEntry)
    {
        return $rowEntry->getAttribute($this->getRowKeyName());
    }

    protected function newQuery()
    {
        $query = $this->getQuery();

        if ($query instanceof ProvidesQuery) {
            return $query->newQuery();
        }

        return $query;
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
            $table = $query->getModel()->getTable();
            $keyName = $query->getModel()->getKeyName();

            $query->orderBy(
                $table.'.'.$keyName, 'DESC'
            );

            if ($this->orderBy) {
                $query->orderBy(
                    $this->orderBy['column'], $this->orderBy['direction']
                );
            } else {
                $query->orderBy(
                    $table.'.'.$keyName, 'DESC'
                );
            }
        }
    }

    protected function applyFilters($query)
    {
        ApplyFilters::dispatch($this->getFilters(), $query, $this->getRequest());
    }

    protected function getRequest()
    {
        return $this->request;
    }

    /** * @return static */
    public static function resolve()
    {
        return app(TableInterface::class);
    }
}
