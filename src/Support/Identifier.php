<?php

namespace SuperV\Platform\Support;

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
    }

    public function parent(): ?Identifier
    {
        if (count($nodes = $this->getNodes()) === 1) {
            return null;
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
        list($type, $typeId) = $this->getTypeNode();

        return $type;
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

    public function getNodes()
    {
        if (! $this->nodes) {
            $this->nodes = explode('.', $this->identifier);
        }

        return $this->nodes;
    }

    public function __toString()
    {
        return $this->identifier;
    }

    public static function make(string $identifier): Identifier
    {
        return new Identifier($identifier);
    }

    protected function getTypeNode()
    {
        $nodes = $this->getNodes();

        $type = array_pop($nodes);
        if (str_contains($type, ':')) {
            list($type, $typeId) = explode(':', $type);
        }

        return [$type, $typeId ?? null];
    }
}
