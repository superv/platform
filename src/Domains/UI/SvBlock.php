<?php

namespace SuperV\Platform\Domains\UI;

class SvBlock implements \JsonSerializable
{
    protected $id;

    protected $component;

    /** @var array */
    protected $props;

    public static function make(string $component)
    {
        $block = new static;
        $block->component = $component;
        $block->id = md5(uniqid());

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
            'id'        => $this->id,
            'component' => $this->component,
            'props'     => array_merge($this->props, ['block-id' => $this->id]),
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