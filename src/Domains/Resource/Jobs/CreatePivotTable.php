<?php

namespace SuperV\Platform\Domains\Resource\Jobs;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;
use SuperV\Platform\Support\Dispatchable;

class CreatePivotTable
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Resource\Relation\RelationConfig
     */
    protected $relation;

    public function __construct(RelationConfig $relation)
    {
        $this->relation = $relation;
    }

    public function handle()
    {
        if ($pivotColumnsCallback = $this->relation->getPivotColumns()) {
            $pivotColumnsCallback($table = new Blueprint(''));
            $this->relation->pivotColumns($table->getColumnNames());
        }

        if (! \Schema::hasTable($this->relation->getPivotTable())) {
            Schema::create(
                $this->relation->getPivotTable(),
                function (Blueprint $table) use ($pivotColumnsCallback) {
                    $table->increments('id');

                    if ($this->relation->type()->isMorphToMany()) {
                        $table->morphs($this->relation->getMorphName());
                    } else {
                        $table->unsignedBigInteger($this->relation->getPivotForeignKey());
                    }

                    $table->unsignedBigInteger($this->relation->getPivotRelatedKey());

                    if ($pivotColumnsCallback) {
                        $pivotColumnsCallback($table);
                    }

                    $table->timestamps();
                    $table->index([$this->relation->getPivotForeignKey()], md5(uniqid()));
                    $table->index([$this->relation->getPivotRelatedKey()], md5(uniqid()));
                });
        }
    }
}