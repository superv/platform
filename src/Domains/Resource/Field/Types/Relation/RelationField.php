<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Relation;

use Illuminate\Database\Eloquent\Relations\BelongsTo as EloquentBelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany as EloquentHasMany;
use Illuminate\Database\Eloquent\Relations\HasOne as EloquentHasOne;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\Contracts\AltersDatabaseTable;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Jobs\CreatePivotTableV2;

class RelationField extends FieldType implements AltersDatabaseTable
{
    public function onMakingConfig(RelationFieldConfig $config)
    {
    }

    public function alterBlueprint(Blueprint $blueprint, array $config = [])
    {
        $config = new RelationFieldConfig($config);

        $blueprint->addPostBuildCallback(function (Blueprint $blueprint) use ($config) {
            if ($localKey = $config->getLocalKey()) {
                $blueprint->addColumn('integer', $localKey, ['nullable' => ! $config->isRequired()]);
            }
        });

        if ($pivotTable = $config->getPivotTable()) {
            (new CreatePivotTableV2)($config);
        }
    }

    public function newQuery(EntryContract $parent)
    {
        $config = $this->getConfig();
        $query = sv_resource($config->getRelated())->newQuery();

        if ($config->getRelationType()->isOneToOne()) {
            if ($config->getLocalKey()) {
                return new EloquentBelongsTo(
                    $query,
                    $parent,
                    $config->getLocalKey(),
                    'id',
                    $this->getName()
                );
            }

            if ($config->getForeignKey()) {
                return new EloquentHasOne(
                    $query,
                    $parent,
                    $config->getForeignKey(),
                    'id'
                );
            }
        }

        if ($config->getRelationType()->isOneToMany()) {
            if ($config->getForeignKey()) {
                return new EloquentHasMany(
                    $query,
                    $parent,
                    $config->getForeignKey() ?? $parent->getForeignKey(),
                    'id'
                );
            }
        }
    }

    public function getConfig(): RelationFieldConfig
    {
        return new RelationFieldConfig($this->field->getConfig());
    }
}
