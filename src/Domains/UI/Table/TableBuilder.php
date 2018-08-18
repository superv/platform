<?php

namespace SuperV\Platform\Domains\UI\Table;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\View\Factory;
use SuperV\Platform\Domains\Entry\EntryModel;
use SuperV\Platform\Domains\UI\Table\Features\BuildTable;
use SuperV\Platform\Support\Concerns\FiresCallbacks;

class TableBuilder implements Responsable
{
    use DispatchesJobs;
    use FiresCallbacks;

    /** @var Table */
    protected $table;

    /** @var string */
    protected $model;

    protected $buttons = [];

    protected $columns;

    protected $beforeCallback;

    protected $filters = [];

    protected $response = [];

    protected $httpRequest;

    /**
     * @var Factory
     */
    private $view;

    public function __construct(Factory $view, Table $table)
    {
        $this->view = $view;
        $this->table = $table;
    }

    public function build()
    {
        $this->fire('ready', ['builder' => $this]);

        $this->dispatch(new BuildTable($this));

        $this->fire('built', ['builder' => $this]);

        return $this;
    }

    /**
     * @return Table
     */
    public function getTable(): Table
    {
        return $this->table;
    }

    /**
     * @param Table $table
     *
     * @return TableBuilder
     */
    public function setTable(Table $table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @return array
     */
    public function getButtons(): array
    {
        return $this->buttons;
    }

    /**
     * @param array $buttons
     *
     * @return TableBuilder
     */
    public function setButtons(array $buttons)
    {
        $this->buttons = $buttons;

        return $this;
    }

    /**
     * @return string|EntryModel
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param string|EntryModel $model
     *
     * @return TableBuilder
     */
    public function setModel($model)
    {
        $this->model = is_object($model) ? get_class($model) : $model;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param mixed $columns
     *
     * @return TableBuilder
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    public function before($callback)
    {
        $this->listen('querying', $callback);

        return $this;
    }

    public function render()
    {
        $this->response = array_has($this->getHttpRequest(), 'config') ? $this->getConfig() : $this->build()->getData();

        return $this;
    }

    public function getHttpRequest()
    {
        return $this->httpRequest ?: request()->all();
    }

    public function getData()
    {
        return [
            'total'      => [
                'results' => $this->table->getOption('total_results'),
            ],
            'rows'       => $this->table->getEntries(),
            'pagination' => $this->table->getData(),
        ];
    }

    public function getConfig()
    {
        return [
            'config' => [
                'cols'    => $this->getColumns(),
                'filters' => $this->getFilters(),
            ],
        ];
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        return response()->json(['data' => $this->response]);
    }
}
