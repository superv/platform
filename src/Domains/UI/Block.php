<?php

namespace SuperV\Platform\Domains\UI;

class Block implements \JsonSerializable
{
    protected $component;

    /** @var array */
    protected $props;

    public static function make(string $component)
    {
        $block = new static;
        $block->component = $component;

        return $block;
    }

    public function props(array $props)
    {
        $this->props = $props;

        return $this;
    }

    public function toArray()
    {
        return [
            'component' => $this->component,
            'props'     => $this->props,
        ];
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}