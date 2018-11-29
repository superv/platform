<?php

namespace SuperV\Platform\Domains\Resource\Filter;

use SuperV\Platform\Domains\Resource\Contracts\ProvidesFilter;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;

class SelectFilter extends Filter
{
    protected $identifier = 'name';

    protected $type = 'select';

    protected $options = [];

    public function onFieldBuilt(Field $field)
    {
        $field->setConfigValue('options', $this->getOptions());
    }

    public function getIdentifier()
    {
        return $this->identifier;
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