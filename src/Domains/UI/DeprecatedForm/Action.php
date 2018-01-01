<?php

namespace SuperV\Platform\Domains\UI\DeprecatedForm;

class Action
{
    private $slug;

    private $type;

    private $options;

    public function __construct($slug, $type, $options)
    {
        $this->slug = $slug;
        $this->type = $type;
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getSlug()
    {
        return $this->slug;
    }
}
