<?php

namespace SuperV\Platform\Domains\Database;

use Closure;
use Illuminate\Database\Connection;

use SuperV\Platform\Domains\Resource\ResourceBlueprint;

class Builder extends \Illuminate\Database\Schema\Builder
{
    /**
     * @var \SuperV\Platform\Domains\Database\Schema
     */
    protected $schema;

    /** @var \SuperV\Platform\Domains\Resource\ResourceBlueprint  */
    protected $resource;

    public function __construct(Connection $connection, Schema $schema)
    {
        parent::__construct($connection);

        $this->schema = $schema;
        $this->resource = new ResourceBlueprint();
    }

    public function create($table, Closure $callback)
    {
        $mainBlueprint = $this->createBlueprint($table);

        $this->build(tap($mainBlueprint, function ($blueprint) use ($callback) {
            $blueprint->create();

            $callback($blueprint, $this->resource);
        }));
    }

    public function table($table, Closure $callback)
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) use ($callback) {

            $callback($blueprint, $this->resource);
        }));
    }

    public function resource(): ?ResourceBlueprint
    {
        return $this->resource;
    }
}