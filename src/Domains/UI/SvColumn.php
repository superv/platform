<?php

namespace SuperV\Platform\Domains\UI;

class SvColumn implements \JsonSerializable
{
    protected $component = 'sv-column';

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