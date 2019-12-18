<?php

namespace SuperV\Platform\Domains\Resource\Builder;

use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldRepository;
use SuperV\Platform\Domains\Resource\ResourceModel;
use SuperV\Platform\Support\Concerns\HasConfig;

class FieldBlueprint
{
    use HasConfig;

    /**
     * @var \SuperV\Platform\Domains\Resource\Builder\Blueprint
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
    protected $fieldHandle = '';

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

    public function __construct(Blueprint $blueprint, string $fieldHandle, string $fieldTypeClass)
    {
        $this->field = FieldFactory::createFromArray(['type' => $fieldTypeClass, 'handle' => $fieldHandle]);
        $this->blueprint = $blueprint;
        $this->fieldHandle = $fieldHandle;
    }

    public function getHandle()
    {
        return $this->field->getHandle();
    }

    public function run(ResourceModel $resource)
    {
        $fieldRepository = FieldRepository::resolve();
        $field = $fieldRepository
            ->getResourceField($resource, $this->fieldHandle)
            ->fill([
                'label'       => str_unslug($this->getLabel() ?? $this->fieldHandle),
                'type'        => $this->field->getType(),
                'column_type' => '',
                'flags'       => $this->flags,
                'rules'       => $this->rules,
                'config'      => $this->getConfig(),
            ]);

        if (! $field->exists()) {
            $field = $fieldRepository->create($field->toArray());
        } else {
            $field->save();
        }

        return $field;
    }

    final public function getConfig()
    {
        return array_merge(
            $this->config,
            array_filter($this->mergeConfig())
        );
    }

    public function mergeConfig(): array
    {
        return [];
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
        $this->config['default_value'] = $value;

        $this->nullable();

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getDefaultValue()
    {
        return $this->config['default_value'] ?? null;
    }

    public function addFlag(string $flag): FieldBlueprint
    {
        $this->flags[] = $flag;

        return $this;
    }

    public function addRule($rule): FieldBlueprint
    {
        $this->rules[] = $rule;

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

    public function showOnLists()
    {
        return $this->addFlag('table.show');
    }

    public function hideOnView()
    {
        return $this->addFlag('view.hide');
    }

    public function hideOnForms()
    {
        return $this->addFlag('hidden');
    }

    public static function make(Blueprint $blueprint, string $fieldName, string $typeClass): FieldBlueprint
    {
        $blueprintClass = sprintf("%sBlueprint", $typeClass);

        if (! class_exists($blueprintClass)) {
            $parts = explode("\\", $typeClass);
            $className = end($parts);
            $blueprintClass = str_replace_last($className, 'Blueprint', $typeClass);
            if (! class_exists($blueprintClass)) {
                $blueprintClass = self::class;
            }
        }

        return new $blueprintClass($blueprint, $fieldName, $typeClass);
    }
}