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
        $fieldConfig = new PolymorphicFieldConfig($config);

        foreach ($fieldConfig->getTypes() as $typeName => $callback) {
            $callback = function (Blueprint $table, ResourceConfig $config) use ($fieldConfig, $callback) {
                $table->belongsTo($fieldConfig->getSelf());
                $callback($table, $config);
            };

            Schema::create($fieldConfig->getSelf().'_'.$typeName, $callback);
        }
    }
}
