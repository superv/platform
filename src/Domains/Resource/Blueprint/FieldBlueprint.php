<?php

namespace SuperV\Platform\Domains\Resource\Blueprint;

use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldRepository;
use SuperV\Platform\Domains\Resource\ResourceModel;
use SuperV\Platform\Support\Concerns\HasConfig;

class FieldBlueprint
{
    use HasConfig;

    /**
     * @var \SuperV\Platform\Domains\Resource\Blueprint\Blueprint
     */
    protected $blueprint;

    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface
     */
    protected $field;

    /**
     * @var string
     */
    protected $label = '';

    /**
     * @var string
     */
    protected $fieldName = '';

    /**
     * @var mixed
     */
    protected $defaultValue;

    /**
     * @var bool
     */
    protected $entryLabel = false;

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @var array
     */
    protected $flags = [];

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
                'label'       => str_unslug($this->getLabel() ?? $this->fieldName),
                'type'        => $this->field->getType(),
                'column_type' => '',
                'flags'       => $this->flags,
                'rules'       => $this->rules,
                'config'      => $this->config,
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

    public function isEntryLabel(): bool
    {
        return $this->entryLabel;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function rules()
    {
        $args = func_get_args();
        if (func_num_args() === 1) {
            $firstArg = $args[0];
            if (is_array($firstArg)) {
                $args = $firstArg;
            } elseif (is_string($firstArg)) {
                $args = explode('|', $firstArg);
            }
        }

        $this->rules = $args;

        return $this;
    }

    public function label(string $label = null): FieldBlueprint
    {
        $this->label = $label;

        return $this;
    }

    public function default($value): FieldBlueprint
    {
        $this->defaultValue = $value;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function addFlag(string $flag): FieldBlueprint
    {
        $this->flags[] = $flag;

        return $this;
    }

    public function hasFlag($flag): bool
    {
        return in_array($flag, $this->flags);
    }

    public function required()
    {
        return $this->addFlag('required');
    }

    public function unique()
    {
        return $this->addFlag('unique');
    }

    public function nullable()
    {
        return $this->addFlag('nullable');
    }

    public function hideOnView()
    {
        return $this->addFlag('view.hide');
    }

    public function hideOnForms()
    {
        return $this->addFlag('hidden');
    }

    public static function make(Blueprint $blueprint, string $fieldName, string $fieldTypeClass): FieldBlueprint
    {
        $fieldBlueprintClass = sprintf("%sBlueprint", $fieldTypeClass);

        if (! class_exists($fieldBlueprintClass)) {
            $fieldBlueprintClass = self::class;
        }

        return new $fieldBlueprintClass($blueprint, $fieldName, $fieldTypeClass);
    }
}