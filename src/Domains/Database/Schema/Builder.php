<?php

namespace SuperV\Platform\Domains\Database\Schema;

use Closure;
use Current;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
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
                'namespace' => Current::migrationScope(),
                'driver'    =>
                    [
                        'type'   => 'database',
                        'params' => [
                            'connection' => $connection->getName(),
                        ],
                    ],
            ]
        );
    }

    public function connection($connection)
    {
        $this->connection = DB::connection($connection);
        $this->connection->useDefaultQueryGrammar();

        $this->resourceConfig->getDriver()->setParam('connection', $connection);

        return $this;
    }

    public function create($table, Closure $callback): ResourceConfig
    {
        $mainBlueprint = $this->createBlueprint($table);
        $this->build(tap($mainBlueprint, function ($blueprint) use ($table, $callback) {
            $this->resourceConfig->setName($table);
            $this->resourceConfig->getDriver()->setParam('table', $table);

            $blueprint->create();

//            $callback($blueprint, $this->resourceConfig);
            app()->call($callback, ['table' => $blueprint, 'config' => $this->resourceConfig]);
        }));

        return $this->resourceConfig;
    }

    public function table($table, Closure $callback)
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) use ($table, $callback) {
            $this->resourceConfig->setName($table);
            $this->resourceConfig->getDriver()->setParam('table', $table);

            $callback($blueprint, $this->resourceConfig);
        }));
    }

    /**
     * Drop a table from the schema if it exists.
     *
     * @param string $table
     * @return void
     */
//    public function dropIfExists($table)
//    {
//        $this->build(tap($this->createBlueprint($table), function ($blueprint) use ($table) {
//            $blueprint->drop();
//        }));
//    }

    public function resource(): ?ResourceConfig
    {
        return $this->resourceConfig;
    }

    protected function fillResourceConfig($table)
    {
//        if (! $this->resourceConfig->getName()) {
//            $this->resourceConfig->setName($table);
//        }
//        $this->resourceConfig->setIdentifier(
//            $this->resourceConfig->getNamespace().'::'.$this->resourceConfig->getName()
//        );

    }
}
