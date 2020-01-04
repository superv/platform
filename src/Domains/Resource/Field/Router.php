<?php

namespace SuperV\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;

class Router
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface
     */
    protected $field;

    public function __construct(FieldInterface $field)
    {
        $this->field = $field;
    }

    public function route(string $route)
    {
        return sv_route('sv::fields.types', [
                'field' => $this->field->getIdentifier(),
                'route' => $route,
            ]
        );
    }

    public function routex(string $route)
    {
        return sv_route('sv::fields.types', [
                'type'  => $this->field->getType(),
                'route' => $route,
            ]
        );
    }
}