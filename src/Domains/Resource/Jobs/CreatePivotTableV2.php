<?php

namespace SuperV\Platform\Domains\Resource\Jobs;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\Field\Types\Relation\RelationFieldConfig;

class CreatePivotTableV2
{
    public function __invoke(RelationFieldConfig $relation)
    {
        if ($pivotColumnsCallback = $relation->getPivotColumns()) {
            $pivotColumnsCallback($table = new Blueprint(''));
            $relation->pivotColumns($table->getColumnNames());
        }

        if (! \Schema::hasTable($relation->getPivotTable())) {
            Schema::create(
                $relation->getPivotTable(),
                function (Blueprint $table) use ($pivotColumnsCallback, $relation) {
                    $table->increments('id');

                    $table->unsignedBigInteger($relation->getPivotForeignKey());

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
