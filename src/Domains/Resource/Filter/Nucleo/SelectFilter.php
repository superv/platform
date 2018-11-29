<?php

namespace SuperV\Modules\Nucleo\Domains\Resource\Table\Filter;

use Illuminate\Support\Collection;
use SuperV\Modules\Nucleo\Domains\Resource\Form\Field\SelectField;

class SelectFilter extends Filter
{
    protected $type = 'select';

    public function options($options)
    {
        if ($options instanceof Collection) {
            $options = $options->all();
        }

        return $this->setConfigValue('options', SelectField::parseOptions($options));
    }

    public function compose(array $params = [])
    {
        if (array_has($this->config, 'options')) {
            $this->setConfigValue('options', SelectField::parseOptions($this->getConfigValue('options')));
        }

        return parent::compose($params);
    }
}