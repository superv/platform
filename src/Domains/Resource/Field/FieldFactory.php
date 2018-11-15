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

        $config = $this->params['config'] ?? null;
        // @TODO:fix
        if (is_string($config)) {
//            $config = array_pull($this->params, 'config');
            $this->params['config'] = json_decode($config, true);
        }


        $rules = $this->params['rules'] ?? null;
        if (is_string($rules)) {
//            $rules = array_pull($this->params, 'rules');
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