<?php

namespace SuperV\Platform\Domains\UI\Table;

class Column implements \JsonSerializable
{
    protected $slug;

    protected $label;

    protected $width;

    public function __construct($slug, $label, $width = null)
    {
        $this->slug = $slug;
        $this->label = $label;
        $this->width = $width;
    }

    public function jsonSerialize()
    {
        return [
            'slug'  => $this->slug,
            'label' => $this->label,
        ];
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }
}