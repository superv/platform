<?php

namespace SuperV\Platform\Domains\Resource\Builder;

use SuperV\Platform\Contracts\Arrayable;

class PrimaryKey implements Arrayable
{
    CONST NUMBER = 'integer';
    CONST TEXT = 'string';
    CONST DEFAULT_STRING_LENGTH = 255;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type = self::NUMBER;

    /**
     * @var array
     */
    protected $options = ['autoincrement' => true, 'unsigned' => true];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function text()
    {
        $this->type = self::TEXT;
        $this->options = ['length' => self::DEFAULT_STRING_LENGTH];

        return $this;
    }

    public function number()
    {
        $this->type = self::NUMBER;

        return $this;
    }

    public function autoincrement($increments = true)
    {
        return $this->setOption('autoincrement', $increments);
    }

    public function setOption(string $key, $val)
    {
        $this->options[$key] = $val;

        return $this;
    }

    public function getOption(string $key)
    {
        return $this->options[$key] ?? null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'name'    => $this->getName(),
            'type'    => $this->getType(),
            'options' => $this->getOptions(),
        ];
    }
}