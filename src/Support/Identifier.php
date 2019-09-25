<?php

namespace SuperV\Platform\Support;

use InvalidArgumentException;

class Identifier
{
    /**
     * @var string
     */
    protected $identifier;

    protected $nodes = [];

    protected function __construct(string $identifier)
    {
        $this->identifier = $identifier;

        if ($this->getNodeCount() < 2) {
            throw new InvalidArgumentException(sprintf("Not a valid identifier string: [%s]", $identifier));
        }
    }

    public function get(): string
    {
        return $this->identifier;
    }

    public function parent(): ?Identifier
    {
        if (count($nodes = $this->getNodes()) === 2) {
            return str_contains($this->identifier, ':') ? static::make(explode(':', $this->identifier)[0]) : null;
        }

        // pop the type node
        array_pop($nodes);

        return static::make(implode('.', $nodes));
    }

    public function getParent(): ?string
    {
        return (string)$this->parent();
    }

    public function getNodeCount()
    {
        return count($this->getNodes());
    }

    public function getType(): ?string
    {
        if ($this->getNodeCount() === 2) {
            return str_contains($this->identifier, ':') ? 'entries' : 'resources';
        }

        list($type, $typeId) = $this->getTypeNode();

        return $type;
    }

    public function withoutType()
    {
        return $this->getParent().'.'.$this->getTypeId();
    }
    public function type(): ?IdentifierType
    {
        return new IdentifierType($this->getType(), $this->getTypeId());
    }

    public function getTypeId()
    {
        list(, $typeId) = $this->getTypeNode();

        return $typeId ?? null;
    }

    public function id()
    {
        return $this->getTypeId();
    }

    public function getNodes()
    {
        if (! $this->nodes) {
            $this->nodes = explode('.', $this->identifier);
        }

        return $this->nodes;
    }

    public function getLastNode()
    {
        return $this->getNodes()[$this->getNodeCount() - 1];
    }

    public function __toString()
    {
        return $this->identifier;
    }

    public function toArray()
    {
        return array_filter([
            'parent'  => $this->parent() ? $this->parent()->get() : null,
            'type'    => $this->getType(),
            'type_id' => $this->getTypeId(),
        ]);
    }

    public static function make(string $identifier): Identifier
    {
        return new Identifier($identifier);
    }

    protected function getTypeNode()
    {
        $nodes = $this->getNodes();

        $type = count($nodes) === 2 ? $this->identifier : array_pop($nodes);
        if (str_contains($type, ':')) {
            list($type, $typeId) = explode(':', $type);
        }

        return [$type, $typeId ?? null];
    }
}
