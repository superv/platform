<?php

namespace SuperV\Platform\Domains\Database\Schema;

use SuperV\Platform\Domains\Resource\Field\Types\Polymorphic\PolymorphicFieldConfig;
use SuperV\Platform\Domains\Resource\Field\Types\Relation\RelationFieldConfig;
use SuperV\Platform\Domains\Resource\Field\Types\Relation\RelationType;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig as Config;

/**
 * Trait CreatesRelations
 * @method \SuperV\Platform\Domains\Resource\ResourceConfig resourceConfig()
 * @method ColumnDefinition addColumn($type, $name, array $parameters = [])
 * @method ColumnDefinition unsignedInteger($column, $autoIncrement = false)
 * @method ColumnDefinition fieldType($type)
 * @method ColumnDefinition fieldName($name)
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

        $config = Config::belongsTo()
                        ->relationName($relationName)
                        ->related($namespace.'.'.$related)
                        ->foreignKey($foreignKey ?? $relationName.'_id')
                        ->ownerKey($ownerKey);

        return $this->unsignedInteger($foreignKey ?? $relationName.'_id')
                    ->fieldType('belongs_to')
                    ->fieldName($relationName)
                    ->relation($config);
    }

    private function splitRelated($related)
    {
        if (\Str::contains($related, '.')) {
            [$namespace, $related] = explode('.', $related);
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
        $config = Config::morphTo()
                        ->morphName($relationName)
                        ->relationName($relationName);

        return $this->addColumn(null, $relationName, ['nullable' => true])->relation($config);
    }

    public function hasOne($related, $relationName, $foreignKey = null, $localKey = null): ColumnDefinition
    {
        $config = Config::hasOne()
                        ->relationName($relationName)
                        ->related($related)
                        ->foreignKey($foreignKey ?? $this->resourceConfig()->getResourceKey().'_id')
                        ->localKey($localKey);

        return $this->addColumn(null, $relationName, ['nullable' => true])->relation($config);
    }

    public function morphOne($related, $relationName, $morphName, $targetModel = null): ColumnDefinition
    {
        $config = Config::morphOne()
                        ->relationName($relationName)
                        ->related($related)
                        ->morphName($morphName)
                        ->targetModel($targetModel);

        return $this->addColumn(null, $relationName, ['nullable' => true])->relation($config);
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
        if (! class_exists($related)) {
            list($namespace, $related) = $this->splitRelated($related);

            $related = $namespace.'.'.$related;
        }

        $config = Config::hasMany()->relationName($relationName)
                        ->related($related)
                        ->foreignKey($foreignKey)
                        ->localKey($localKey);

        return $this->addColumn(null, $relationName, ['nullable' => true])->relation($config);
    }

    public function morphToMany($related, $relation, $morphName): Config
    {
        if (! class_exists($related)) {
            list($namespace, $related) = $this->splitRelated($related);

            $related = $namespace.'.'.$related;
        }

        $config = Config::morphToMany()
                        ->relationName($relation)
                        ->related($related)
                        ->morphName($morphName)
                        ->pivotForeignKey($morphName.'_id');

        $this->addColumn(null, $relation, ['nullable' => true])->relation($config);

        return $config;
    }

    public function morphMany($related, $relationName, $morphName)
    {
        $config = Config::morphMany()
                        ->relationName($relationName)
                        ->related($related)
                        ->morphName($morphName);

        return $this->addColumn(null, $relationName, ['nullable' => true])->relation($config);
    }

    protected function prepareNamespace(Config $config, string $related)
    {
    }
}
