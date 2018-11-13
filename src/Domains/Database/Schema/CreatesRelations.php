<?php

namespace SuperV\Platform\Domains\Database\Schema;

use Closure;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig as Config;

trait CreatesRelations
{
    public function nullableBelongsTo($related, $relation, $foreignKey = null, $ownerKey = null)
    {
        return $this->belongsTo($related, $relation, $foreignKey, $ownerKey)->nullable();
    }

    public function belongsTo($related, $relationName, $foreignKey = null, $ownerKey = null)
    {
        $this->addColumn(null, $relationName, ['nullable' => true])
             ->relation(
                 Config::belongsTo()
                       ->relationName($relationName)
                       ->related($related)
                       ->foreignKey($foreignKey ?? $relationName.'_id')
                       ->ownerKey($ownerKey)
             );

        return $this->unsignedInteger($foreignKey ?? $relationName.'_id')
                    ->fieldType('belongs_to')
                    ->fieldName($relationName)
                    ->config(
                        Config::belongsTo()
                              ->relationName($relationName)
                              ->related($related)
                              ->foreignKey($foreignKey ?? $relationName.'_id')
                              ->ownerKey($ownerKey)
                              ->toArray()
                    );
    }

    public function hasOne($related, $relationName, $foreignKey, $localKey = null)
    {
        return $this->addColumn(null, $relationName, ['nullable' => true])
                    ->relation(
                        Config::hasOne()
                              ->relationName($relationName)
                              ->related($related)
                              ->foreignKey($foreignKey)
                              ->localKey($localKey)
                    );
    }

    public function morphOne($related, $relationName, $morphName, $targetModel = null)
    {
        return $this->addColumn(null, $relationName, ['nullable' => true])
                    ->relation(
                        Config::morphOne()
                              ->relationName($relationName)
                              ->related($related)
                              ->morphName($morphName)
                              ->targetModel($targetModel)
                    );
    }

    public function morphTo($relationName)
    {
        return $this->addColumn(null, $relationName, ['nullable' => true])
                    ->relation(
                        Config::morphTo()
                              ->relationName($relationName)
                    );
    }

    public function belongsToMany(
        $related,
        $relationName,
        $pivotTable = null,
        $pivotForeignKey = null,
        $pivotRelatedKey = null,
        Closure $pivotColumns = null
    ) {
        return $this->addColumn(null, $relationName, ['nullable' => true])
                    ->relation(
                        Config::belongsToMany()
                              ->relationName($relationName)
                              ->related($related)
                              ->pivotTable($pivotTable)
                              ->pivotForeignKey($pivotForeignKey)
                              ->pivotRelatedKey($pivotRelatedKey)
                              ->pivotColumns($pivotColumns)
                    );
    }

    public function hasMany($related, $relationName, $foreignKey, $localKey = null)
    {
        return $this->addColumn(null, $relationName, ['nullable' => true])
                    ->relation(
                        Config::hasMany()
                              ->relationName($relationName)
                              ->related($related)
                              ->foreignKey($foreignKey)
                              ->localKey($localKey)
                    );
    }

    public function morphToMany(
        $related,
        $relationName,
        $morphName,
        $pivotTable = null,
        $pivotRelatedKey = null,
        Closure $pivotColumns = null
    ) {
        return $this->addColumn(null, $relationName, ['nullable' => true])
                    ->relation(
                        Config::morphToMany()
                              ->relationName($relationName)
                              ->related($related)
                              ->pivotTable($pivotTable)
                              ->pivotForeignKey($morphName.'_id')
                              ->pivotRelatedKey($pivotRelatedKey)
                              ->pivotColumns($pivotColumns)
                              ->morphName($morphName)
                    );
    }
}