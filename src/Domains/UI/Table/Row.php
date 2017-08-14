<?php namespace SuperV\Platform\Domains\UI\Table;

use Illuminate\Foundation\Bus\DispatchesJobs;
use SuperV\Platform\Domains\UI\Button\Features\MakeButtons;

class Row
{
    use DispatchesJobs;

    protected $model;

    protected $buttons;

    private $columns;

    public function __construct($model, $columns, $buttons)
    {
        $this->model = $model;
        $this->buttons = $buttons;
        $this->columns = $columns;
    }

    public function make()
    {
       $this->buttons = $this->dispatch(new MakeButtons($this->buttons, ['entry' => $this->model]));

       return $this;
    }

    /**
     * @return mixed
     */
    public function getButtons()
    {
        return $this->buttons;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }
}