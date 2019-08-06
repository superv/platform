<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Relation;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\Contracts\AltersDatabaseTable;
use SuperV\Platform\Domains\Resource\Field\FieldType;

class RelationField extends FieldType implements AltersDatabaseTable
{
    public function alterBlueprint(Blueprint $blueprint, array $config = [])
    {
        $blueprint->addPostBuildCallback(function (Blueprint $blueprint) use ($config) {
            if ($localKey = array_get($config, 'local_key')) {
                $blueprint->addColumn('integer', $localKey);
            }
        });
    }
}
