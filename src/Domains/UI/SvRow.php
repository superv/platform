<?php

namespace SuperV\Platform\Domains\UI;

class SvRow implements \JsonSerializable
{
    protected $component = 'SvRow';

    protected $blocks = [];

    protected $slug;

    /**
     * @param string $slug
     * @return static
     */
    public static function make(string $slug = null)
    {
        $self = new static;
        $self->slug = $slug;
        return $self;
    }

    public function addColumn($slug = null)
    {
        $this->add($column = SvColumn::make($slug));

        return $column;
    }

    public function add($block)
    {
        $this->blocks[] = $block;
        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'component' => $this->component,
            'props' => [
                'blocks' => $this->blocks
            ]
        ];
    }}