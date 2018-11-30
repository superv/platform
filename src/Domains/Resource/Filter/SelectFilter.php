<?php

namespace SuperV\Platform\Domains\Resource\Filter;

use SuperV\Platform\Domains\Resource\Field\Contracts\Field;

class SelectFilter extends Filter
{
    protected $type = 'select';

    protected $options = [];

    public function onFieldBuilt(Field $field)
    {
        $field->setConfigValue('options', $this->getOptions());
        $field->setConfigValue('placeholder', $this->getLabel());
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): Filter
    {
        $this->options = $options;

        return $this;
    }
}