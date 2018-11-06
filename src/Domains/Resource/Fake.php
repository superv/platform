<?php

namespace SuperV\Platform\Domains\Resource;

use Faker\Generator;
use SuperV\Platform\Domains\Resource\Field\Field;

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

    public function __construct(Resource $resource, array $overrides = [])
    {
        $this->resource = $resource;
        $this->overrides = $overrides;
    }

    public function __invoke()
    {
        $this->faker = app(Generator::class);

        $attributes = $this->makeAttributes();
        $attributes['id'] = $this->overrides['id'] ?? null;

        return $this->resource->create(array_filter_null($attributes));
    }

    protected function fake(Field $field)
    {
        if (!$field->hasEntry()) {
            return null;
        }
        if ($value = array_get($this->overrides, $field->getColumnName())) {
            return $value;
        }
        if (method_exists($this, $method = camel_case('fake_'.$field->getType()))) {
            return $this->$method($field);
        }

        return $this->faker->text;
    }

    protected function fakeText(Field $field)
    {
        if ($fake = $this->faker->__get(camel_case($field->getName()))) {
            return $fake;
        }

        return $this->faker->text;
    }

    protected function fakeTextarea(Field $field)
    {
        return $this->faker->text;
    }

    protected function fakeNumber(Field $field)
    {
        if ($field->getConfigValue('type') === 'decimal') {
            $max = $field->getConfigValue('total', 5) - 2;

            return $this->faker->randomFloat($field->getConfigValue('places', 2), 0, $max);
        }

        return $field->getName() === 'age' ? $this->faker->numberBetween(10, 99) : $this->faker->randomNumber();
    }

    protected function fakeEmail(Field $field)
    {
        return $this->faker->safeEmail;
    }

    protected function fakeBoolean(Field $field)
    {
        return $this->faker->boolean;
    }

    protected function fakeDatetime(Field $field)
    {
        return $field->getConfigValue('time') ? $this->faker->dateTime : $this->faker->date();
    }

    /**
     * @return mixed
     */
    public function makeAttributes()
    {
        $attributes = $this->resource->getFields()->map(function (Field $field) {
            if ($field->show() && $field->hasColumn()) {
                return [$field->getColumnName(), $this->fake($field)];
            }
        })->filter()->toAssoc()->all();

        return $attributes;
    }

    public static function create(Resource $resource, array $overrides = [])
    {
        return (new static($resource, $overrides))();
    }
}