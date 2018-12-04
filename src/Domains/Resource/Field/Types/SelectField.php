<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFilter;
use SuperV\Platform\Domains\Resource\Filter\SelectFilter;
use SuperV\Platform\Support\Composer\Payload;

class SelectField extends FieldType implements NeedsDatabaseColumn, ProvidesFilter
{
    protected $placeholder;

    protected function boot()
    {
        $this->on('form.composing', $this->composer());
        $this->on('view.presenting', $this->presenter());
        $this->on('table.presenting', $this->presenter());
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
            // Add a null value placeholder if not exists
            if (! is_null(array_first($options)['value'])) {
                $options = array_merge([['value' => null, 'text' => $this->getPlaceholder()]],
                    $options
                );
            }

            $payload->set('meta.options', $options);
        };
    }

    public function makeFilter()
    {
        return SelectFilter::make($this->getName(), $this->getLabel())
                           ->setOptions($this->getOptions());
    }

    public function getOptions()
    {
        return $this->field->getConfigValue('options', []);
    }

    public function getPlaceholder()
    {
        return $this->field->getPlaceholder() ?? $this->getLabel();
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