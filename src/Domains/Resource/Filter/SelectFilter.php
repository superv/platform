<?php

namespace SuperV\Platform\Domains\Resource\Filter;

use SuperV\Platform\Domains\Resource\Field\Contracts\Field;

class SelectFilter extends Filter
{
    protected $identifier = 'name';

    protected $type = 'select';

    protected $options = [];

    public function setOptions(array $options): Filter
    {
        $this->options = $options;

        return $this;
    }

    public function onFieldBuilt(Field $field)
    {
        $field->setConfigValue('options', $this->options);
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }
}