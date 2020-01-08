<?php

namespace SuperV\Platform\Support;

use InvalidArgumentException;

class Ident
{
    /**
     * @var string
     */
    protected $identifier;

    protected $nodes = [];

    protected function __construct(string $identifier)
    {
        $this->identifier = $identifier;
        $this->nodes = explode('.', $this->identifier);

        if ($this->getNodeCount() < 2) {
            throw new InvalidArgumentException(sprintf("Ident string must contain at least 2 nodes: [%s]", $identifier));
        }
    }

    public function get(): string
    {
        return $this->identifier;
    }

    public function parent(): ?Ident
    {
        if (count($nodes = $this->getNodes()) === 2) {
            return str_contains($this->identifier, ':') ? static::make(explode(':', $this->identifier)[0]) : null;
        }

        // pop the type node
        array_pop($nodes);

        return static::make(implode('.', $nodes));
    }

    public function vendor()
    {
        return $this->nodes[0];
    }

    public function addon()
    {
        return $this->vendor().'.'.$this->nodes[1];
    }

    public function resource()
    {
        return $this->addon().'.'.$this->nodes[2];
    }

    public function getParent(): ?string
    {
        return (string)$this->parent();
    }

    public function getNamespace()
    {
        return $this->getNodes()[0];
    }

    public function getResource()
    {
        return $this->getNodes()[1];
    }

    public function isNamespace($namespace): bool
    {
        return $this->getNamespace() === $namespace;
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

        [$type,] = $this->getTypeNode();

        return $type;
    }

    public function withoutType()
    {
        return $this->getParent().'.'.$this->handle();
    }

//    public function type(): ?IdentType
//    {
//        return new IdentType($this->getType(), $this->handle());
//    }

    public function handle()
    {
        [, $handle] = $this->getTypeNode();

        return $handle ?? null;
    }

    public function id()
    {
        return $this->handle();
    }

    public function getNodes()
    {
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
            'type_id' => $this->handle(),
        ]);
    }

    public static function make(string $identifier): Ident
    {
        return new Ident($identifier);
    }

    protected function getTypeNode()
    {
        $nodes = $this->getNodes();

        $type = count($nodes) === 2 ? $this->identifier : array_pop($nodes);
        if (str_contains($type, ':')) {
            [$type, $typeId] = explode(':', $type);
        }

        return [$type, $typeId ?? null];
    }
}
