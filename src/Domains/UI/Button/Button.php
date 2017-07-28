<?php namespace SuperV\Platform\Domains\UI\Button;

use Illuminate\View\Factory;

class Button
{
    public $key;

    public $attributes;

    public $text;

    public $icon;

    public $class;

    public $type = 'default';

    /**
     * @var Factory
     */
    private $view;

    public function __construct(Factory $view)
    {
        $this->view = $view;
    }

    public function render()
    {
        return $this->view->make('superv::button.button', ['button' => $this]);
    }

}