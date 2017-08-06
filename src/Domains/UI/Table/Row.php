<?php namespace SuperV\Platform\Domains\UI\Table;

use Illuminate\Foundation\Bus\DispatchesJobs;
use SuperV\Platform\Domains\UI\Button\Features\MakeButtons;

class Row
{
    use DispatchesJobs;

    protected $model;

    protected $buttons;

    public function __construct($model, $buttons)
    {
        $this->model = $model;
        $this->buttons = $buttons;
    }

    public function make()
    {
        $args   = [
                    'entry' => [
                        'id' => 7,
                        'name' => 'Entiri'
                    ]
                ];
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