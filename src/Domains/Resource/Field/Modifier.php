<?php

namespace SuperV\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\Contracts\HasModifier;

class Modifier
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Contracts\HasModifier
     */
    protected $modifible;

    public function __construct(HasModifier $modifible)
    {
        $this->modifible = $modifible;
    }

    public function set(array $params = [])
    {
        $callback = $this->modifible->getModifier();

        return app()->call($callback, $params);
    }
}