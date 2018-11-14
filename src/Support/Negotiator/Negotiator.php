<?php

namespace SuperV\Platform\Support\Negotiator;

use ReflectionClass;

class Negotiator
{
    /**
     * @var array
     */
    protected $strategies;

    public function __construct(array $strategies)
    {
        $this->strategies = $strategies;
    }

    public function handshake(Provider $provider, Requirer $requirer)
    {
        $requirements = $requirer->getRequirements();
        $resolvers = $provider->getProvidings();
        $negotiation = array_intersect($resolvers, $requirements);

        foreach ($negotiation as $requirement) {
            $strategy = $this->getStrategyFor($requirement);
            $strategyMethod = static::getFirstMethod($strategy);
            $value = $provider->{$strategyMethod}();

            $requirementMethod = static::getFirstMethod($requirement);
            $requirer->{$requirementMethod}($value);
        }
    }

    protected function getStrategyFor($requirement)
    {
        return $this->strategies[$requirement];
    }

    private static function getFirstMethod($class)
    {
        $reflection = new ReflectionClass($class);

        return $reflection->getMethods()[0]->getName();
    }
}