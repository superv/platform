<?php

namespace SuperV\Platform\Domains\Resource\Blueprint;

use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldRepository;
use SuperV\Platform\Domains\Resource\ResourceModel;

class FieldBlueprint
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface
     */
    protected $field;

    /**
     * @var \SuperV\Platform\Domains\Resource\Blueprint\Blueprint
     */
    protected $blueprint;

    /**
     * @var string
     */
    protected $fieldName;

    protected $entryLabel = false;

    public function __construct(Blueprint $blueprint, string $fieldName, string $fieldTypeClass)
    {
        $this->field = FieldFactory::createFromArray(['type' => $fieldTypeClass, 'name' => $fieldName]);
        $this->blueprint = $blueprint;
        $this->fieldName = $fieldName;
    }

    public function getName()
    {
        return $this->field->getName();
    }

    public function run(ResourceModel $resource)
    {
        $fieldRepository = FieldRepository::resolve();
        $field = $fieldRepository
            ->getResourceField($resource, $this->fieldName)
            ->fill([
                'label'       => str_unslug($this->fieldName),
                'type'        => $this->field->getType(),
                'column_type' => '',
                'flags'       => [],
                'rules'       => [],
                'config'      => [],
            ]);

        if (! $field->exists()) {
            $field = $fieldRepository->create($field->toArray());
        } else {
            $field->save();
        }

        return $field;
    }

    public function getField(): \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface
    {
        return $this->field;
    }

    public function useAsEntryLabel(): FieldBlueprint
    {
        $this->entryLabel = true;

        return $this;
    }

    public function isEntryLabel(): bool
    {
        return $this->entryLabel;
    }
}