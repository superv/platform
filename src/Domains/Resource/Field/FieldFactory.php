<?php

namespace SuperV\Platform\Domains\Resource\Field;


class FieldFactory
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Field\FieldModel
     */
    protected $fieldEntry;

    /**
     * @var array
     */
    protected $params;

    protected function create(): Field
    {
        $config = array_pull($this->params, 'config');
        $rules = array_pull($this->params, 'rules');

        // @TODO:fix
        if (is_string($config)) {
            $this->params['config'] = json_decode($config, true);
        }
        if (is_string($rules)) {
            $this->params['rules'] = json_decode($rules, true);
        }

        return new Field($this->params);
    }

    public static function createFromEntry(FieldModel $entry): Field
    {
        $factory = new static;
        $factory->params = $entry->toArray();

        return $factory->create();
    }

    public static function createFromArray(array $params): Field
    {
        $factory = new static;
        $factory->params = $params;

        return $factory->create();
    }

}