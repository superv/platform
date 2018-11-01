<?php

namespace SuperV\Platform\Domains\Resource;

use SuperV\Platform\Support\Concerns\HasConfig;

class ColumnFieldMapper
{
    use HasConfig;

    protected $columnType;

    protected $fieldType;

    protected $parameters = [];

    protected $rules = [];

    public static function for($columnType): self
    {
        return (new static())->setColumnType($columnType);
    }

    /**
     * @param mixed $columnType
     * @return ColumnFieldMapper
     */
    public function setColumnType($columnType)
    {
        $this->columnType = $columnType;

        return $this;
    }

    public function map(array $parameters = null)
    {
        if ($parameters) {
            $this->setParameters($parameters);
        }
        $mapperMethod = camel_case('map_'.snake_case($this->columnType));

        $this->$mapperMethod();

        return $this;
    }

    /**
     * @param mixed $parameters
     * @return ColumnFieldMapper
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    protected function mapString()
    {
        $this->fieldType = 'text';
        if ($length = $this->getParameter('length')) {
            $this->addRule('max:'.$length);
        }
    }

    public function getParameter($key)
    {
        return array_get($this->parameters, $key);
    }

    public function addRule($rule)
    {
        $this->rules[] = $rule;
        $this->rules = array_unique($this->rules);
    }

    protected function mapMediumText()
    {
        $this->mapText();
    }

    protected function mapText()
    {
        $this->fieldType = 'textarea';
    }

    protected function mapLongText()
    {
        $this->mapText();
    }

    protected function mapTinyInteger()
    {
        $this->mapInteger();
    }

    protected function mapInteger()
    {
        $this->fieldType = 'number';
        $this->addRule('integer');
        $this->setConfigValue('type', 'integer');

        if ($this->getParameter('unsigned') === true) {
            $this->addRule('min:0');
        }
    }

    protected function mapBigInteger()
    {
        $this->mapInteger();
    }

    protected function mapDecimal()
    {
        $this->fieldType = 'number';
        $this->addRule('numeric');
        $this->setConfigValue('type', 'decimal');

        if ($this->getParameter('unsigned') === true) {
            $this->addRule('min:0');
        }

        $this->setConfigValue('total', $this->getParameter('total'));
        $this->setConfigValue('places', $this->getParameter('places'));
    }

    protected function mapBoolean()
    {
        $this->fieldType = 'boolean';
    }

    protected function mapEnum()
    {
        $this->fieldType = 'select';

        $this->setConfigValue('options', $this->getParameter('allowed'));
    }

    protected function mapUuid()
    {
        $this->fieldType = 'text';
    }

    protected function mapDate()
    {
        $this->fieldType = 'date';
    }

    /**
     * @return mixed
     */
    public function getFieldType()
    {
        return $this->fieldType;
    }

    /**
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }
}