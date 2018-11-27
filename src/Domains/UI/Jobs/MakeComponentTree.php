<?php

namespace SuperV\Platform\Domains\UI\Jobs;

use SuperV\Platform\Domains\Resource\Contracts\ProvidesUIComponent;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Dispatchable;

class MakeComponentTree
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Resource\Contracts\ProvidesUIComponent
     */
    protected $provider;

    public function __construct(ProvidesUIComponent $provider)
    {
        $this->provider = $provider;
    }

    public function handle()
    {
        $component = $this->provider->makeComponent();

        $this->scanComponent($component);

        return $component;
    }

    protected function scanComponent(ComponentContract $component)
    {
        $component->getProps()->transform(function ($prop) {
            return $this->scanProp($prop);
        });
    }

    protected function scanProp($prop)
    {
        if (is_array($prop) || is_iterable($prop)) {
            foreach ($prop as $key => $value) {
                if ($value instanceof ProvidesUIComponent) {
                    $prop[$key] = self::dispatch($value);
                } elseif ($value instanceof ComponentContract) {
                    $this->scanComponent($value);
                } else {
                    $prop[$key] = $this->scanProp($value);
                }
            }
        }

        return $prop;
    }
}