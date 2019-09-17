<?php

namespace SuperV\Platform\Domains\Database\Schema;

use Closure;
use SuperV\Platform\Domains\Resource\Field\Types\Polymorphic\PolymorphicFieldConfig;
use SuperV\Platform\Domains\Resource\Field\Types\Relation\RelationFieldConfig;
use SuperV\Platform\Domains\Resource\Field\Types\Relation\RelationType;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig as Config;

/**
 * Trait CreatesRelations
 * @method \SuperV\Platform\Domains\Resource\ResourceConfig resourceConfig()
 * @method ColumnDefinition addColumn($type, $name, array $parameters = [])
 *
 * @package SuperV\Platform\Domains\Database\Schema
 */
trait CreatesRelations
{
    public function polymorph($relationName): PolymorphicFieldConfig
    {
        $config = PolymorphicFieldConfig::make();

        $config->setSelf($this->resourceConfig()->getTable());

        $this->morphs('type');

        $this->addColumn(null, $relationName, ['nullable' => true])
             ->fieldType('polymorphic')
             ->fieldName($relationName)
             ->config($config);

        return $config;
    }

    public function relatedToOne($related, string $relationName = null)
    {
        list($namespace, $related) = $this->splitRelated($related);
        $relationName = $relationName ?? str_singular($related);

        return $this->relation($relationName, RelationType::oneToOne())
                    ->related($namespace.'.'.$related);
    }

    public function relatedToMany($related, string $relationName = null)
    {
        list($namespace, $related) = $this->splitRelated($related);
        $relationName = $relationName ?? $related;

        return $this->relation($relationName, RelationType::oneToMany())
                    ->related($namespace.'.'.$related);
    }

    public function relatedManyToMany($related, string $relationName = null)
    {
        list($namespace, $related) = $this->splitRelated($related);
        $relationName = $relationName ?? $related;

        return $this->relation($relationName, RelationType::manyToMany())
                    ->related($namespace.'.'.$related);
    }

    public function relation($relationName, RelationType $relationType): RelationFieldConfig
    {
        $config = RelationFieldConfig::make();
        $config->type($relationType);
        $config->setSelf($this->resourceConfig()->getTable());

        $this->addColumn(null, $relationName, ['nullable' => true])
             ->fieldType('relation')
             ->fieldName($relationName)
             ->config($config);

        return $config;
    }

    public function nullableBelongsTo($related, $relation, $foreignKey = null, $ownerKey = null): ColumnDefinition
    {
        return $this->belongsTo($related, $relation, $foreignKey, $ownerKey)->nullable();
    }

    public function belongsTo($related, $relationName = null, $foreignKey = null, $ownerKey = null): ColumnDefinition
    {
        list($namespace, $related) = $this->splitRelated($related);
        $relationName = $relationName ?? str_singular($related);

        return $this->unsignedInteger($foreignKey ?? $relationName.'_id')
                    ->fieldType('belongs_to')
                    ->fieldName($relationName)
                    ->relation(
                        Config::belongsTo()
                              ->relationName($relationName)
                              ->related($namespace.'.'.$related)
                              ->foreignKey($foreignKey ?? $relationName.'_id')
                              ->ownerKey($ownerKey)
                    );
    }

    private function splitRelated($related)
    {
        if (str_contains($related, '.')) {
            list($namespace, $related) = explode('.', $related);
        } else {
            $namespace = $this->resourceConfig()->getNamespace();
        }

        return [$namespace, $related];
    }

    public function nullableMorphTo($relationName)
    {
        return $this->morphTo($relationName)->nullable();
    }

    public function morphTo($relationName): ColumnDefinition
    {
        return $this->addColumn(null, $relationName, ['nullable' => true])
                    ->relation(Config::morphTo()
                                     ->morphName($relationName)
                                     ->relationName($relationName));
    }

    public function hasOne($related, $relationName, $foreignKey = null, $localKey = null): ColumnDefinition
    {
        return $this->addColumn(null, $relationName, ['nullable' => true])
                    ->relation(
                        Config::hasOne()
                              ->relationName($relationName)
                              ->related($related)
                              ->foreignKey($foreignKey ?? $this->resourceConfig()->getResourceKey().'_id')
                              ->localKey($localKey)
                    );
    }

    public function morphOne($related, $relationName, $morphName, $targetModel = null): ColumnDefinition
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

    public function belongsToMany($related, $relation): Config
    {
        $config = Config::belongsToMany()
                        ->relationName($relation)
                        ->related($related);

        $this->addColumn(null, $relation, ['nullable' => true])
             ->fieldType('belongs_to_many')
             ->fieldName($relation)
             ->relation($config);

        return $config;
    }

    public function hasMany($related, $relationName, $foreignKey = null, $localKey = null): ColumnDefinition
    {
        return $this->addColumn(null, $relationName, ['nullable' => true])
                    ->relation(
                        Config::hasMany()
                              ->relationName($relationName)
                              ->related($related)
//                              ->foreignKey($foreignKey ?? $this->resourceBlueprint()->getResourceKey().'_id')
                              ->foreignKey($foreignKey)
                              ->localKey($localKey)
                    );
    }

    public function morphToMany(
        $related,
        $relationName,
        $morphName,
        $pivotTable,
        $pivotRelatedKey,
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

    public function morphMany($related, $relationName, $morphName)
    {
        return $this->addColumn(null, $relationName, ['nullable' => true])
                    ->relation(
                        Config::morphMany()
                              ->relationName($relationName)
                              ->related($related)
                              ->morphName($morphName)
                    );
    }
}
