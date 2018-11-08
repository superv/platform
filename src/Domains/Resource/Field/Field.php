<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Illuminate\Support\Str;
use SuperV\Platform\Domains\Resource\Concerns\Watchable;

/**
 * Class Field  IMMUTABLE!!!!!!!!
 *
 * @package SuperV\Platform\Domains\Resource\Field
 */
class Field
{
    use Watchable;

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


    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
        $this->uuid = Str::uuid()->toString();

        $this->value = new FieldValue($this);
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

        $this->notifyWatchers($this->value);
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