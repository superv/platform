<?php

namespace SuperV\Platform\Domains\Database;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Support\Fluent;

class Builder extends \Illuminate\Database\Schema\Builder
{
    /**
     * @var \SuperV\Platform\Domains\Database\Schema
     */
    protected $schema;

    public function __construct(Connection $connection, Schema $schema)
    {
        parent::__construct($connection);
        $this->schema = $schema;
    }

    public function create($table, Closure $callback)
    {
        $mainBlueprint = $this->createBlueprint($table);

        $this->build(tap($mainBlueprint, function ($blueprint) use ($callback) {
            $blueprint->create();

            $callback($blueprint);
        }));

        /**
         * Create a second table for translations if schema is translatable
         */
        if ($this->schema->isTranslatable()) {
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
}