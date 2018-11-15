<?php

namespace SuperV\Platform\Support\Negotiator;

use ReflectionClass;

class Negotiator
{
    const PROVIDE = 'provide';
    const PROVIDES = 'Provides';
    const ACCEPT = 'accept';
    const ACCEPTS = 'Accepts';

    /**
     * @var array
     */
    protected $strategies;

    protected $providers = [];

    protected $acceptors = [];

    public function __construct()
    {
        $this->strategies = collect();
    }

    public function handshake(array $parties)
    {
        collect($parties)->map(function ($party) { $this->scan($party); });

        foreach ($this->acceptors as $meta => $acceptor) {
            if ($provider = $this->providers[$meta]) {
                $this->negotiate($acceptor, $meta, $provider);
            }
        }

//        $this->makeStrategies();

//        foreach ($this->strategies as $requirement => $providing) {
//            $providingMethod = static::getFirstMethod($providing);
//            $provider = $this->providers[$providing];
//            $value = $provider->{$providingMethod}();
//
//            $requirementMethod = static::getFirstMethod($requirement);
//            $requirer = $this->acceptors[$requirement];
//            $requirer->{$requirementMethod}($value);
//        }
    }

    protected function negotiate($acceptor, $meta, $provider)
    {
        $acceptor->{static::ACCEPT.$meta}(
            $provider->{static::PROVIDE.$meta}()
        );
    }

    /**
     * Scan all parties and disperse acceptors and providers
     *
     * @param $party
     */
    protected function scan($party)
    {
        $implements = class_implements($party);
        foreach ($implements as $interface) {
            if (starts_with($basename = class_basename($interface), self::PROVIDES)) {
                $meta = str_replace_first(self::PROVIDES, '', $basename);
                $this->providers[$meta] = $party;
//                $this->providers[$interface] = [
//                    'provider' => $party,
//                    'meta'     => str_replace_first(self::PROVIDES, '', $basename),
//                ];
            } elseif (starts_with($basename = class_basename($interface), self::ACCEPTS)) {
                $meta = str_replace_first(self::ACCEPTS, '', $basename);
                $this->acceptors[$meta] = $party;
//                $this->acceptors[$interface] = [
//                    'acceptor' => $party,
//                    'meta'     => str_replace_first(self::ACCEPTS, '', $basename),
//                ];
            }
        }
    }

    protected function makeStrategies()
    {
        foreach ($this->acceptors as $requirement => $requirer) {
            $providingName = self::PROVIDES.str_replace_first(self::ACCEPTS, '', class_basename($requirement));

            if ($providing = $this->searchForProviding($providingName)) {
                $this->strategies[$requirement] = $providing;
            }
        }
    }

    protected function searchForProviding($providingName)
    {
        foreach ($this->providers as $providing => $provider) {
            if (class_basename($providing) === $providingName) {
                return $providing;
            }
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