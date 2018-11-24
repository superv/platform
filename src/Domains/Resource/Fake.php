<?php

namespace SuperV\Platform\Domains\Resource;

use Faker\Generator;
use Illuminate\Http\UploadedFile;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field as FieldContract;
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

    /** @var array */
    protected $attributes = [];

    public function __construct()
    {
        $this->faker = app(Generator::class);
    }

    public function __invoke(Resource $resource, array $overrides = [])
    {
        $this->resource = $resource;
        $this->overrides = $overrides;

        $resource->getFields()
                 ->map(function (FieldContract $field) {
                     if (! $field->isHidden() && ! $field->doesNotInteractWithTable()) {
                         $this->attributes[$field->getColumnName()] = $this->fake($field);
                     }
                 })->filter()
                 ->toAssoc()
                 ->all();

        return array_filter_null($this->attributes);
    }

    protected function fake(FieldContract $field)
    {
        if ($value = array_get($this->overrides, $field->getColumnName())) {
            return $value;
        }
        if (method_exists($this, $method = camel_case('fake_'.$field->getType()))) {
            return $this->$method($field);
        }

        return $this->faker->text;
    }

    protected function fakeBelongsTo($field)
    {
        $relatedResource = ResourceFactory::make($field->getConfigValue('related_resource'));

        if ($relatedResource->count() < 5) {
            $relatedResource->fake([], rand(2, 10));
        }

        return $relatedResource->newQuery()->inRandomOrder()->value('id');
    }

    protected function fakeFile()
    {
        return new UploadedFile(SV_TEST_BASE.'/__fixtures__/square.png', 'square.png');
    }

    protected function fakeText(FieldContract $field)
    {
        $fieldName = $field->getName();

        if (! in_array($fieldName, ['label', 'bio']) && $fake = $this->faker->__get(camel_case($fieldName))) {
            return $fake;
        }

        return $this->faker->text;
    }

    protected function fakeTextarea()
    {
        return $this->faker->text;
    }

    protected function fakeNumber($field)
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

    protected function fakeDatetime($field)
    {
        return $field->getConfigValue('time') ? $this->faker->dateTime : $this->faker->date();
    }

    public static function field(Field $field)
    {
        return (new static)->fake($field);
    }

    public static function create(Resource $resource, array $overrides = [])
    {
        return $resource->create(static::make($resource, $overrides));
    }

    public static function make(Resource $resource, array $overrides = [])
    {
        return (new static())->__invoke($resource, $overrides);
    }
}