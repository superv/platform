<?php

namespace SuperV\Platform\Domains\UI\Components;

use Illuminate\Contracts\Support\Responsable;
use SuperV\Platform\Domains\UI\Components\Concerns\StyleHelper;
use SuperV\Platform\Support\Composer\Composable;
use SuperV\Platform\Support\Composer\Payload;
use SuperV\Platform\Support\Concerns\FiresCallbacks;

abstract class BaseComponent implements ComponentContract, Composable, Responsable
{
    use FiresCallbacks;
    use StyleHelper;

    /** @var \SuperV\Platform\Domains\UI\Components\Props */
    protected $props;

    protected $name;

    protected $uuid;

    protected $classes = [];

    /**
     * Composer tokens
     *
     * @var array
     */
    protected $tokens = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ComponentContract
    {
        $this->name = $name;

        return $this;
    }

    public function __construct(array $props = [])
    {
        $this->setProps($props);
    }

    public function addClass(string $class): ComponentContract
    {
        $this->classes[] = $class;

        return $this;
    }

    public function uuid()
    {
        return $this->uuid;
    }

    public function getProps(): Props
    {
        return $this->props;
    }

    public function setProps($props): ComponentContract
    {
        $this->props = new Props($props);

        return $this;
    }

    public function compose(\SuperV\Platform\Support\Composer\Tokens $tokens = null)
    {
        $payload = new Payload([
            'component' => $this->getName(),
            'uuid'      => $this->uuid(),
            'props'     => $this->getProps(),
            'classes'   => implode(' ', $this->getClasses()),
        ]);

        $this->fire('composed', ['payload' => $payload]);

        return $payload->toArray();
    }

    public function getHandle(): string
    {
        return 'cmp';
    }

    public function getClasses(): array
    {
        return $this->classes;
    }

    public function getProp($key)
    {
        return $this->props->get($key);
    }

    public function setProp($key, $value): ComponentContract
    {
        $this->props->set($key, $value);

        return $this;
    }

    public function toResponse($request)
    {
        return response()->json(['data' => sv_compose($this, $this->tokens)]);
    }

    public function withTokens(array $tokens): ComponentContract
    {
        $this->tokens = $tokens;

        return $this;
    }


    /** return @static */
    public static function make($name = '')
    {
        $static = new static;
        if ($name) {
            $static->name = $name;
        }

        return $static;
    }
}