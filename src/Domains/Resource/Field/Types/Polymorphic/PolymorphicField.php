<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Polymorphic;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\Field\Contracts\AltersDatabaseTable;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\ResourceConfig;

class PolymorphicField extends FieldType implements AltersDatabaseTable
{
    public function alterBlueprint(Blueprint $blueprint, array $config = [])
    {
        $config = new PolymorphicFieldConfig($config);

        foreach ($config->getTypes() as $typeName => $callback) {
            $callback = function (Blueprint $table, ResourceConfig $resourceConfig) use ($config, $callback) {
                $table->belongsTo($config->getSelf());
                $callback($table, $resourceConfig);
            };

            Schema::create($config->getSelf().'_'.$typeName, $callback);
        }
    }
}
