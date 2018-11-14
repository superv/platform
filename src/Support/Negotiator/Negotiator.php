<?php

namespace SuperV\Platform\Support\Negotiator;

use ReflectionClass;

class Negotiator
{
    const PROVIDES = 'Provides';

    const REQUIRES = 'Requires';

    /**
     * @var array
     */
    protected $strategies;

    protected $providings = [];

    protected $requirements = [];

    public function __construct()
    {
        $this->strategies = collect();
    }

    public function handshake(array $parties)
    {
        collect($parties)->map(function ($party) { $this->scan($party); });

        $this->makeStrategies();


        foreach ($this->strategies as $requirement => $providing) {
            $providingMethod = static::getFirstMethod($providing);
            $provider = $this->providings[$providing];
            $value = $provider->{$providingMethod}();

            $requirementMethod = static::getFirstMethod($requirement);
            $requirer = $this->requirements[$requirement];
            $requirer->{$requirementMethod}($value);
        }
    }

    protected function getStrategyFor($requirement)
    {
        return $this->strategies[$requirement];
    }

    protected function scan($party)
    {
        $implements = class_implements($party);
        foreach ($implements as $interface) {
            if (starts_with(class_basename($interface), self::PROVIDES)) {
                if (array_key_exists(Providing::class, class_implements($interface))) {
                    $this->providings[$interface] = $party;
                }
            } elseif (starts_with(class_basename($interface), self::REQUIRES)) {
                if (array_key_exists(Requirement::class, class_implements($interface))) {
                    $this->requirements[$interface] = $party;
                }
            }
        }
    }

    protected function makeStrategies()
    {
        foreach ($this->requirements as $requirement => $requirer) {
            $providingName = self::PROVIDES.str_replace_first(self::REQUIRES, '', class_basename($requirement));

            if ($providing = $this->searchForProviding($providingName)) {
                $this->strategies[$requirement] = $providing;
            }
        }
    }

    protected function searchForProviding($providingName)
    {
        foreach ($this->providings as $providing => $provider) {
            if (class_basename($providing) === $providingName) {
                return $providing;
            }
        }
    }

    private static function getFirstMethod($class)
    {
        $reflection = new ReflectionClass($class);

        return $reflection->getMethods()[0]->getName();
    }
}