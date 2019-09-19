<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Contracts\Arrayable;
use SuperV\Platform\Domains\Database\Schema\ColumnDefinition;
use SuperV\Platform\Domains\Resource\ColumnFieldMapper;
use SuperV\Platform\Domains\Resource\Field\Contracts\AltersDatabaseTable;
use SuperV\Platform\Domains\Resource\Field\Contracts\RequiresDbColumn;
use SuperV\Platform\Domains\Resource\Field\FieldRepository;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Field\Rules;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\Relation\Contracts\ProvidesField;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;
use SuperV\Platform\Domains\Resource\Relation\RelationRepository;
use SuperV\Platform\Domains\Resource\ResourceModel;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Exceptions\ValidationException;

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
            return;
        }

        $this->setResourceEntry($event);

//        $this->mapFieldType($this->column);

        if ($this->column->relation) {
            $relationConfig = $this->column->getRelationConfig();

            $relationType = $this->createRelations($relationConfig);

            if (! $relationType instanceof ProvidesField) {
                /**
                 * If this column holds only relation data
                 * no need to continue further. We should
                 * also set ignore to true not to create
                 * any table columns for this field
                 */
                $this->column->ignore(true);

                return;
            }

            $this->column->config($relationConfig->toArray());
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

        try {
            $this->persistEntry();
        } catch (ValidationException $e) {
            PlatformException::throw($e);
        }

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
        $relationType = RelationRepository::make()
                                          ->create(
                                              $this->resource,
                                              $relationConfig,
                                              $this->blueprint,
                                              ! $this->column->nullable
                                          );

        return $relationType;
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

        if (! $this->field->exists()) {
            $this->repository->create($this->field->toArray());
        } else {
            $this->field->save();
        }
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
