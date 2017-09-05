<?php

namespace SuperV\Platform\Domains\UI\Table;

use Illuminate\Foundation\Bus\DispatchesJobs;
use SuperV\Platform\Domains\Entry\EntryModel;
use SuperV\Platform\Domains\UI\Button\Button;
use SuperV\Platform\Domains\UI\Button\Features\MakeButtons;
use SuperV\Platform\Support\Decorator;
use SuperV\Platform\Support\Template;

class Row
{
    use DispatchesJobs;

    /** @var  EntryModel */
    protected $entry;

    protected $buttons;

    private $columns;

    /**
     * @var TableBuilder
     */
    private $builder;

    public function __construct(TableBuilder $builder, $model, $columns, $buttons)
    {
        $this->entry = $model;
        $this->buttons = $buttons;
        $this->columns = $columns;
        $this->builder = $builder;
    }

    public function make()
    {
        $this->buttons = $this->dispatch(new MakeButtons($this->buttons, ['entry' => $this->entry]));

        $this->buttons = array_map(function (Button $button) {
            return $button->setIconOnly(true);
        }, $this->buttons);

        return $this;
    }

    public function getContent(Column $column)
    {
        $term = 'entry';

        $value = $column->getField();

        if (is_string($value) && preg_match("/^{$term}.([a-zA-Z\\_]+)/", $value, $match)) {

            $payload[$term] = $entry = (new Decorator())->decorate($this->entry);
            $value = app(Template::class)->render("{{ {$value}|raw }}", $payload);
        } else {
            $value = $this->entry->getAttribute($column->getField());
        }

        return $value;
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
    public function getEntry()
    {
        return $this->entry;
    }
}
