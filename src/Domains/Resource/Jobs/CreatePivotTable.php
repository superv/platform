<?php

namespace SuperV\Platform\Domains\Resource\Jobs;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Exceptions\PlatformException;

class CreatePivotTable
{
    public function __invoke(RelationConfig $relation)
    {
        if ($pivotColumnsCallback = $relation->getPivotColumns()) {
            $pivotColumnsCallback($table = new Blueprint(''));
            $relation->pivotColumns($table->getColumnNames());
        }

        if (! $pivotTable = $relation->getPivotTable()) {
            PlatformException::runtime("Pivot table can not be null");
        }
        if (! \Schema::hasTable($pivotTable)) {
            Schema::create($pivotTable,

                function (Blueprint $table, ResourceConfig $config) use ($pivotColumnsCallback, $relation) {
                    /**
                     * Set pivot resource identifier
                     */
                    $config->setIdentifier($relation->getPivotIdentifier());

                    $table->increments('id');

                    if ($relation->type()->isMorphToMany()) {
                        $table->morphs($relation->getMorphName());
                    } else {
                        $table->unsignedBigInteger($relation->getPivotForeignKey());
                    }

                    $table->unsignedBigInteger($relation->getPivotRelatedKey());

                    if ($pivotColumnsCallback) {
                        $pivotColumnsCallback($table);
                    }

                    $table->timestamps();
                    $table->index([$relation->getPivotForeignKey()]);
                    $table->index([$relation->getPivotRelatedKey()]);
                });
        }
    }
}
