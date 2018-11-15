<?php

namespace SuperV\Platform\Domains\Resource;

use Faker\Generator;
use SuperV\Platform\Domains\Resource\Field\DoesNotInteractWithTable;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;

class Fake
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    /**
     * @var array
     */
    protected $overrides;

    /** @var \Faker\Generator */
    protected $faker;

    /** @var array */
    protected $attributes = [];

    public function __construct(Resource $resource, array $overrides = [])
    {
        $this->resource = $resource;
        $this->overrides = $overrides;
    }

    public function __invoke()
    {
        $this->faker = app(Generator::class);

        $this->resource->getFields()->map(function ($field) {
            if ($field instanceof FieldModel) {
                $field = FieldFactory::createFromEntry($field);
            }
//            if ($field instanceof Field) {
//                $field = FieldModel::withUuid($field->uuid());
//            }
            $fieldType = $field->resolveType();
//            $fieldType = FieldType::fromEntry($field);

            if ($fieldType->visible() && !$fieldType instanceof DoesNotInteractWithTable) {
                $this->attributes[$fieldType->getColumnName()] = $this->fake($fieldType);
            }
        })->filter()->toAssoc()->all();

        return $this->resource->create(array_filter_null($this->attributes));
    }

    protected function fake(FieldType $field)
    {
        if ($value = array_get($this->overrides, $field->getColumnName())) {
            return $value;
        }
        if (method_exists($this, $method = camel_case('fake_'.$field->getType()))) {
            return $this->$method($field);
        }

        return $this->faker->text;
    }

    protected function fakeText(FieldType $field)
    {
        if ($fake = $this->faker->__get(camel_case($field->getName()))) {
            return $fake;
        }

        return $this->faker->text;
    }

    protected function fakeTextarea()
    {
        return $this->faker->text;
    }

    protected function fakeNumber(FieldType $field)
    {
        if ($field->getConfigValue('type') === 'decimal') {
            $max = $field->getConfigValue('total', 5) - 2;

            return $this->faker->randomFloat($field->getConfigValue('places', 2), 0, $max);
        }

        return $field->getName() === 'age' ? $this->faker->numberBetween(10, 99) : $this->faker->randomNumber();
    }

    protected function fakeEmail()
    {
        return $this->faker->safeEmail;
    }

    protected function fakeBoolean()
    {
        return $this->faker->boolean;
    }

    protected function fakeDatetime(FieldType $field)
    {
        return $field->getConfigValue('time') ? $this->faker->dateTime : $this->faker->date();
    }

    public static function create(Resource $resource, array $overrides = [])
    {
        return (new static($resource, $overrides))();
    }
}