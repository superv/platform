<?php

namespace SuperV\Platform\Testing;

use Tests\Platform\TestCase;

class FormComponent extends HelperComponent
{
    /** @var \Tests\Platform\TestCase */
    protected $testCase;

    /** @var \Illuminate\Support\Collection */
    protected $fields;

    public function assertIdentifier($expected)
    {
        TestCase::assertEquals($expected, $this->getProp('identifier'));
    }

    public function getFieldCount()
    {
        return $this->getFields()->count();
    }

    public function getFields()
    {
        if (! $this->fields) {
            $this->fields = collect($this->getProp('fields'))
                ->keyBy(function ($field) {
                    return $field['handle'];
                });
        }

        return $this->fields;
    }

    public function assertFieldKeys($keys)
    {
        $diff = $this->getFields()->keys()->diff($keys);
        $this->testCase->assertTrue($diff->isEmpty(), 'Keys: '.implode(',', $diff->all()));
    }

    public function getField($name, $key = null)
    {
        $field = $this->getFields()->get($name);
        if (is_null($key)) {
            return $field;
        }

        return array_get($field, $key);
    }

    public function getFieldValues()
    {
        return collect($this->getFields())->map(function ($field) {
            return [$field['handle'], $field['value'] ?? null];
        })->toAssoc()->all();
    }

    public static function get($identifier, $testCase, $entry = null)
    {
        if (class_exists($identifier)) {
            $identifier = $identifier::$identifier;
        }

        $url = sv_route('sv::forms.display', ['form' => $identifier, 'entry' => $entry]);

        $response = $testCase->getJsonUser($url);
        if (! $response->isOk()) {
            dd($response->decodeResponseJson());
        }

        $self = static::fromArray($response->decodeResponseJson('data'));
        $self->testCase = $testCase;

        return $self;
    }
}
