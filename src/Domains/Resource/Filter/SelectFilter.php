<?php

namespace SuperV\Platform\Domains\Resource\Filter;

use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;

class SelectFilter extends Filter
{
    protected $type = 'select';

    protected $options = [];

    public function onFieldBuilt(FieldInterface $field)
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