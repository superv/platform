<?php

namespace SuperV\Platform\Domains\Resource\Resource;

class IndexFields
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Resource\Fields
     */
    protected $fields;

    public function __construct(Fields $fields)
    {
        $this->fields = $fields;
    }

    public function show($name, $label = null)
    {
        $field = $this->fields->get($name);
        $field->showOnIndex();

        if ($label) {
            $field->setLabel($label);
        }

        return $field;
    }
}