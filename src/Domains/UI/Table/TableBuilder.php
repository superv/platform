<?php

namespace SuperV\Platform\Domains\UI\Table;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\View\Factory;
use SuperV\Platform\Domains\Entry\EntryModel;
use SuperV\Platform\Domains\UI\Table\Features\BuildTable;
use SuperV\Platform\Traits\FiresCallbacks;

class TableBuilder
{
    use DispatchesJobs;
    use FiresCallbacks;

    /** @var Table */
    protected $table;

    /** @var string */
    protected $model;

    protected $wrapper = 'superv::table.layout';

    protected $buttons = [];

    protected $columns;

    /**
     * @var Factory
     */
    private $view;

    public function __construct(Factory $view, Table $table)
    {
        $this->view = $view;
        $this->table = $table;
        $this->wrapper = '';
    }

    public function make()
    {
        $this->build();
        $this->post();
        $this->load();

        return $this;
    }

    public function load()
    {
//        $this->dispatch(new LoadTable($this));
//        $this->dispatch(new AddAssets($this));
//        $this->dispatch(new MakeTable($this));

        return $this;
    }

    /**
     * Trigger post operations
     * for the table.
     *
     * @return $this
     */
    public function post()
    {
        if (app('request')->isMethod('post')) {
//            $this->dispatch(new PostTable($this));
        }

        return $this;
    }

    public function render()
    {
        $this->make();

        $this->build();

        if (! $this->wrapper) {
            return $this->table->render();
        }

        return $this->view->make($this->wrapper, ['table' => $this->table]);
    }

    public function build()
    {
        $this->fire('ready', ['builder' => $this]);

        $this->dispatch(new BuildTable($this));

        $this->fire('built', ['builder' => $this]);
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
    public function setTable(Table $table): TableBuilder
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
    public function setButtons(array $buttons): TableBuilder
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
     * @param string $model
     *
     * @return TableBuilder
     */
    public function setModel(string $model): TableBuilder
    {
        $this->model = $model;

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

    /**
     * @return string
     */
    public function getWrapper(): string
    {
        return $this->wrapper;
    }

    /**
     * @param string $wrapper
     *
     * @return TableBuilder
     */
    public function setWrapper(string $wrapper): TableBuilder
    {
        $this->wrapper = $wrapper;

        return $this;
    }

    public function noWrapper()
    {
        $this->wrapper = null;

        return $this;
    }
}
