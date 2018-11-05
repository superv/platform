<?php

namespace SuperV\Platform\Domains\Database;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Support\Fluent;

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
    }

    public function create($table, Closure $callback)
    {
        $mainBlueprint = $this->createBlueprint($table);

        $this->resource = new ResourceBlueprint();

        $this->build(tap($mainBlueprint, function ($blueprint) use ($callback) {
            $blueprint->create();

            $callback($blueprint, $this->resource);
        }));

        /**
         * Create a second table for translations if schema is translatable
         */
        if (false) {
            $blueprint = $this->createBlueprint($table.'_translations');

            $blueprint->create();

            $blueprint->increments('id');
            $blueprint->unsignedInteger('entry_id');
            $blueprint->string('locale');

            collect($mainBlueprint->getColumns())
                ->map(function (Fluent $column) use ($blueprint) {
                    if (in_array($column->type, ['string', 'text'])) {
                        $attrs = $column->getAttributes();
                        $blueprint->addColumn(
                            array_pull($attrs, 'type'),
                            array_pull($attrs, 'name'),
                            $attrs
                        );
                    }
                });

            $this->build($blueprint);
        }
    }

    public function resource(): ResourceBlueprint
    {
        return $this->resource;
    }
}