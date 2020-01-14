<?php

namespace SuperV\Platform\Domains\Database\Schema;

use Closure;
use Current;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use SuperV\Platform\Domains\Resource\Jobs\GetTableResource;
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
        $this->build(tap($mainBlueprint,
                function (Blueprint $blueprint) use ($table, $callback) {
                    $this->resourceConfig->handle($table);
                    $this->resourceConfig->getDriver()->setParam('table', $table);

                    $blueprint->create();

                    app()->call($callback, [
                        'table'  => $blueprint,
                        'config' => $this->resourceConfig,
                    ]);
                })
        );

        return $this->resourceConfig;
    }

    public function table($table, Closure $callback)
    {
        $resource = GetTableResource::dispatch($table, $this->connection->getName());
        $this->resourceConfig = ResourceConfig::find($resource);

        $this->build(tap($this->createBlueprint($table),
                function (Blueprint $blueprint) use ($table, $callback) {
                    $this->resourceConfig->handle($table);
                    $this->resourceConfig->getDriver()->setParam('table', $table);

                    app()->call($callback, [
                        'table'  => $blueprint,
                        'config' => $this->resourceConfig,
                    ]);
                })
        );
    }

    public function resource(): ?ResourceConfig
    {
        return $this->resourceConfig;
    }
}
