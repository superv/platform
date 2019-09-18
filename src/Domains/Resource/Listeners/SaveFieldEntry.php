<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Contracts\Arrayable;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\ColumnDefinition;
use SuperV\Platform\Domains\Resource\ColumnFieldMapper;
use SuperV\Platform\Domains\Resource\Field\Contracts\AltersDatabaseTable;
use SuperV\Platform\Domains\Resource\Field\Contracts\RequiresDbColumn;
use SuperV\Platform\Domains\Resource\Field\FieldRepository;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Field\Rules;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\Jobs\CreatePivotTable;
use SuperV\Platform\Domains\Resource\Relation\Contracts\ProvidesField;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;
use SuperV\Platform\Domains\Resource\ResourceModel;

class SaveFieldEntry
{
    /** @var \SuperV\Platform\Domains\Resource\ResourceModel */
    protected $resource;

    /** @var \SuperV\Platform\Domains\Resource\ResourceConfig */
    protected $resourceConfig;

    /** @var \SuperV\Platform\Domains\Resource\Field\Contracts\Field */
    protected $fieldType;

    protected $fieldWithoutEloquent = true;

    /** @var \SuperV\Platform\Domains\Database\Schema\ColumnDefinition */
    protected $column;

    /** @var \Illuminate\Support\Collection|\SuperV\Platform\Domains\Database\Schema\ColumnDefinition[] */
    protected $allColumns;

    /** @var \SuperV\Platform\Domains\Database\Schema\Blueprint */
    protected $blueprint;

    /** @var array */
    protected $config;

    /** @var \SuperV\Platform\Domains\Resource\Field\FieldModel|null */
    protected $field;

    /**
     * @var \SuperV\Platform\Domains\Resource\Field\FieldRepository
     */
    protected $repository;

    public function __construct(FieldRepository $fieldRepository)
    {
        $this->repository = $fieldRepository;
    }

    public function handle($event)
    {
        $this->column = $event->column;

        $this->blueprint = $event->blueprint;

        $this->resourceConfig = $event->config;

        /**
         * Do not create a field for the ID column
         * ..for now
         */
        if ($this->column->autoIncrement) {
//            $this->column->addFlag('table.show');
            return;
        }

        $this->setResourceEntry($event);

//        $this->mapFieldType($this->column);

        /**
         * If this column holds only relation data
         * we wont be creating a field for it.
         * (this shouldn't be here yes..🤫)
         */
        if ($this->column->relation && ! $this->createRelations($this->column->getRelationConfig())) {
            return;
        }

        if (! $this->column->fieldType) {
            $this->mapFieldType($this->column);
        }

        $this->fieldType = FieldType::resolveType($this->column->fieldType);

        $this->config = $this->makeConfig($this->column);

        if (! $this->fieldType instanceof RequiresDbColumn) {
            $this->column->ignore();
        }

        if ($this->fieldType instanceof AltersDatabaseTable) {
            $this->fieldType->alterBlueprint($this->blueprint, $this->config);
        }

        $this->persistEntry();

        /**
         * Attach field to default resource form
         */
        if ($formEntry = FormModel::findByResource($this->resource->getId())) {
            $formEntry->attachField($this->field->getId());
        }
    }

    protected function makeConfig(ColumnDefinition $column)
    {
        if (method_exists($this->fieldType, 'onMakingConfig')) {
            $this->fieldType->onMakingConfig($column->config);
        }

        $config = $column->config instanceof Arrayable ? $column->config->toArray() : $column->config;

        $config['default_value'] = $column->getDefaultValue();

        return array_filter_null($config);
    }

    protected function createRelations(RelationConfig $relationConfig)
    {
        $this->column->ignore();
//
//        RelationRepository::make()->create(
//            $this->resource,
//            $relationConfig
//        );

        $relationType = Relation::resolve($relationConfig->getType());

        if ($relationConfig->hasPivotTable()) {
            (new CreatePivotTable)($relationConfig);
        }

        $this->resource->resourceRelations()->create([
            'uuid'   => uuid(),
            'name'   => $relationConfig->getName(),
            'type'   => $relationConfig->getType(),
            'config' => $relationConfig->toArray(),
        ]);

        if ($relationType instanceof ProvidesField) {
            $this->column->ignore(false);
            $this->column->config($relationConfig->toArray());

            return true; // should continue creating field
        }

        if ($relationConfig->type()->isMorphTo()) {
            $name = $relationConfig->getName();

            $this->blueprint->addPostBuildCallback(
                function (Blueprint $blueprint) use ($name) {
                    $blueprint->string("{$name}_type")->nullable($this->column->nullable);

                    $blueprint->unsignedBigInteger("{$name}_id")->nullable($this->column->nullable);
                    $blueprint->index(["{$name}_type", "{$name}_id"]);
                }
            );

            $morphToField = $this->resource->makeField($name);
            $morphToField->fill([
                'type'   => 'morph_to',
                'config' => RelationConfig::morphTo()
                                          ->relationName($name)
                                          ->toArray(),
                'flags'  => ['nullable'],
            ]);
            $morphToField->save();

            return false;  // should NOT create field
        }
    }

    protected function mapFieldType(ColumnDefinition $column)
    {
        $mapper = ColumnFieldMapper::for($column->type)->map($column->toArray());

        $column->config(array_merge($column->getConfig(), $mapper->getConfig()));

        $column->rules(
            Rules::make($mapper->getRules())
                 ->merge($column->getRules())
                 ->get()
        );

        $column->fieldType($mapper->getFieldType());
    }

    protected function persistEntry()
    {
        $column = $this->column;

        $this->field = $this->repository
            ->getResourceField($this->resource, $column->getFieldName())
            ->fill([
                'type'        => $column->fieldType,
                'column_type' => $column->type,
                'flags'       => $column->flags,
                'rules'       => Rules::make($column->getRules())->get(),
                'config'      => $this->config,
            ]);

        $this->field->save();
    }

    protected function setResourceEntry($event): void
    {
        if (isset($event->model)) {
            $resourceEntry = ResourceModel::withModel($event->model);
        }
        if (! isset($resourceEntry)) {
            $resourceEntry = ResourceModel::withIdentifier($this->resourceConfig->getIdentifier());
        }

        if (! $resourceEntry) {
//            dd(DB::table('sv_resources')->where('name','t_posts')->get());
            throw new \Exception(sprintf("Error saving field entry [%s]: Resource model entry not found for table [%s]", $this->field, $this->resourceConfig->getIdentifier()));
        }

        $this->resource = $resourceEntry;
    }
}
