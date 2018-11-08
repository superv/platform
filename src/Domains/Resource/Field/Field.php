<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Illuminate\Support\Str;

/**
 * Class Field  IMMUTABLE!!!!!!!!
 *
 * @package SuperV\Platform\Domains\Resource\Field
 */
class Field
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var \SuperV\Platform\Domains\Resource\Field\FieldValue
     */
    protected $value;

    /**
     * @var array
     */
    protected $observers = [];

    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
        $this->uuid = Str::uuid()->toString();

        $this->value = new FieldValue($this);
    }

    public function attach(FieldObserver $observer)
    {
        $this->observers[] = $observer;

        return $this;
    }

    public function detach(FieldObserver $detach)
    {
        $this->observers = collect($this->observers)->filter(function (FieldObserver $observer) use($detach) {
            return $observer !== $detach;
        })->filter()->values()->all();

        return $this;
    }

    public function notify()
    {
        collect($this->observers)->map(function (FieldObserver $observer) {
            $observer->fieldValueUpdated($this->value());
        });
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function value(): FieldValue
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value->set($value);
        $this->notify();
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label ?? str_unslug($this->name);
    }

    public function compose(): array
    {
        return [
            'type'  => $this->getType(),
            'uuid'  => $this->uuid(),
            'name'  => $this->getName(),
            'label' => $this->getLabel(),
        ];
    }
}