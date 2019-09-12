<?php

namespace SuperV\Platform\Domains\Database\Schema;

use Closure;
use Illuminate\Database\Connection;
use SuperV\Platform\Domains\Resource\ResourceConfig;

class Builder extends \Illuminate\Database\Schema\Builder
{
    /**
     * @var \SuperV\Platform\Domains\Database\Schema\Schema
     */
    protected $schema;

    /** @var \SuperV\Platform\Domains\Resource\ResourceConfig */
    protected $resourceConfig;

    public function __construct(Connection $connection, Schema $schema)
    {
        parent::__construct($connection);

        $this->schema = $schema;
        $this->resourceConfig = ResourceConfig::make(
            [
                'driver' =>
                    [
                        'type'   => 'database',
                        'params' => [
                            'connection' => $connection->getName(),
                        ],
                    ],
            ]
        );
    }

    public function create($table, Closure $callback): ResourceConfig
    {
        $mainBlueprint = $this->createBlueprint($table);

        $this->build(tap($mainBlueprint, function ($blueprint) use ($table, $callback) {
            $this->resourceConfig->setIdentifier($table);
            $this->resourceConfig->getDriver()->setParam('table', $table);

            $blueprint->create();

            $callback($blueprint, $this->resourceConfig);
        }));

        return $this->resourceConfig;
    }

    public function table($table, Closure $callback)
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) use ($table, $callback) {
            $this->resourceConfig->setIdentifier($table);
            $this->resourceConfig->getDriver()->setParam('table', $table);

            $callback($blueprint, $this->resourceConfig);
        }));
    }

    public function resource(): ?ResourceConfig
    {
        return $this->resourceConfig;
    }
}
