<?php

namespace SuperV\Platform\Domains\UI;

class Chart
{
    protected $title;

    protected $htmlClass = '';

    protected $type = 'line';

    protected $chartData;

    public static function make($title)
    {
        return new self($title);
    }

    public function __construct($title)
    {
        $this->title = $title;
    }

    public function class($class)
    {
        $this->htmlClass .= ' '.$class;

        return $this;
    }

    public function type($type)
    {
        $this->type = $type;
        return $this;
    }

    public function get($group = 'date', $callback = null)
    {
        return [
            'component' => 'SvChart',
            'props'     => [
                'title'        => $this->title,
                'type'         => $this->type,
                'data'         => (new ChartData())->get($group, $callback),
            ],
            'class'     => $this->htmlClass,
        ];
    }

    /**
     * @param mixed $chartData
     * @return Chart
     */
    public function chartData($chartData)
    {
        $this->chartData = $chartData;

        return $this;
    }
}