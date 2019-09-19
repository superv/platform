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

    public function parent()
    {
        if (count($nodes = $this->getNodes()) === 1) {
            return null;
        }

        // pop the type node
        array_pop($nodes);

        return static::make(implode('.', $nodes));
    }

    public function type()
    {
        list($type, $typeId) = $this->getTypeNode();

        return new IdentifierType(str_singular($type), $typeId);
    }

    public function typeId()
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
