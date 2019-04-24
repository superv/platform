<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Resource\Contracts\ProvidesFilter;
use SuperV\Platform\Domains\Resource\Field\Contracts\RequiresDbColumn;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Filter\SelectFilter;
use SuperV\Platform\Support\Composer\Payload;

class SelectField extends FieldType implements RequiresDbColumn, ProvidesFilter
{
    protected function boot()
    {
        $this->field->on('form.composing', $this->composer());
        $this->field->on('view.presenting', $this->presenter());
        $this->field->on('table.presenting', $this->presenter());
    }

    protected function presenter()
    {
        return function ($value) {
            $options = $this->getOptions();

            return array_get($options, $value, $value);
        };
    }

    protected function composer()
    {
        return function (Payload $payload) {
            $options = static::parseOptions(($this->getOptions()));

            $payload->set('meta.options', $options);
            $payload->set('placeholder', trans('sv::resource.select', ['resource' => $this->field->getPlaceholder()]));
        };
    }

    public function makeFilter(?array $params = [])
    {
        return SelectFilter::make($this->getName(), $this->field->getLabel())
                           ->setOptions($this->getOptions());
    }

    public function getOptions()
    {
        return $this->getConfigValue('options', []);
    }

    public static function parseOptions(array $options = [])
    {
        if (! empty($options) && ! is_array(array_first($options))) {
            return array_map(function ($value) use ($options) {
                return ['value' => $value, 'text' => $options[$value]];
            }, array_keys($options));
        }

        return $options;
    }
}