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

        if (method_exists($this, $mapperMethod)) {
            $this->$mapperMethod();
        }

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

    public function getParameter($key)
    {
        return array_get($this->parameters, $key);
    }

    public function addRule($rule)
    {
        $this->rules[] = $rule;
        $this->rules = array_unique($this->rules);
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

    public static function for(string $columnType): self
    {
        return (new static())->setColumnType($columnType);
    }

    protected function mapString()
    {
        $this->fieldType = 'text';
        if ($length = $this->getParameter('length')) {
            $this->setConfigValue('length', $length);
        }
    }

    protected function mapChar()
    {
        $this->fieldType = 'text';
        if ($length = $this->getParameter('length')) {
            $this->setConfigValue('length', $length);
        }
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

    protected function mapInteger($max = ['signed' => 2147483647, 'unsigned' => 4294967295])
    {
        $this->fieldType = 'number';
        $this->setConfigValue('type', 'integer');
        $this->setConfigValue('unsigned', $this->getParameter('unsigned'));
        $this->addRule('max:'.$max[$this->getParameter('unsigned') ? 'unsigned' : 'signed']);
    }

    protected function mapTinyInt()
    {
        $this->mapTinyInteger();
    }

    protected function mapTinyInteger()
    {
        $this->mapInteger(['signed' => 127, 'unsigned' => 255]);
    }

    protected function mapBigInteger()
    {
        $this->mapInteger();
    }

    protected function mapBigInt()
    {
        $this->mapInteger();
    }

    protected function mapSmallInt()
    {
        $this->mapSmallInteger();
    }

    protected function mapSmallInteger()
    {
        $this->mapInteger(['signed' => 32767, 'unsigned' => 65535]);
    }

    protected function mapMediumInt()
    {
        $this->mapMediumInteger();
    }

    protected function mapMediumInteger()
    {
        $this->mapInteger(['signed' => 8388607, 'unsigned' => 16777215]);
    }

    protected function mapDouble()
    {
        $this->mapDecimal();
    }

    protected function mapDecimal()
    {
        $this->fieldType = 'number';
        $this->setConfigValue('type', 'decimal');

        $this->setConfigValue('total', $this->getParameter('total'));
        $this->setConfigValue('places', $this->getParameter('places'));

        $integer = $this->getParameter('total') - $this->getParameter('places');
        if ($integer) {
            $this->addRule('max:'.(pow(10, $integer) - pow(10, -$this->getParameter('places'))));
        }
    }

    protected function mapFloat()
    {
        $this->mapDecimal();
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
        $this->fieldType = 'date_time';
        $this->setConfigValue('time', false);
    }

    protected function mapTimestamp()
    {
        $this->mapDateTime();
    }

    protected function mapDateTime()
    {
        $this->fieldType = 'date_time';
        $this->setConfigValue('time', true);
    }
}
