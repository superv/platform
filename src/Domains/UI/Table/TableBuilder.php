<?php namespace SuperV\Platform\Domains\UI\Table;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\View\Factory;
use SuperV\Modules\Auth\Domains\User\UserModel;
use SuperV\Platform\Domains\UI\Button\Features\MakeButtons;
use SuperV\Platform\Domains\UI\Table\Features\BuildTable;
use SuperV\Platform\Traits\FiresCallbacks;

class TableBuilder
{
    use DispatchesJobs;
    use FiresCallbacks;

    /** @var  Table */
    protected $table;

    /** @var string  */
    protected $model = UserModel::class;

    protected $buttons = [
        'edit' => [
            'href' => 'auth/users/{entry.id}/edit',
            'text' => 'Edit',
            'type' => 'info',
        ],
        'delete' => [
            'href' => 'auth/users/{entry.id}/delete',
            'text' => 'Delete',
            'type' => 'danger',
        ],
    ];

    /**
     * @var Factory
     */
    private $view;

    public function __construct(Factory $view)
    {
        $this->view = $view;
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

        return $this->build();
    }

    public function build()
    {
        $this->fire('ready', ['builder' => $this]);

        $this->dispatch(new BuildTable($this));

        $this->fire('built', ['builder' => $this]);

        return $this->view->make('superv::table.layout', ['table' => $this->table]);
    }

    /**
     * @return Table
     */
    public function getTable(): Table
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
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
}