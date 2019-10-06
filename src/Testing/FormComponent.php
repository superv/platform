<?php

namespace SuperV\Platform\Testing;

use Tests\Platform\TestCase;

class FormComponent extends HelperComponent
{
    /** @var \Tests\Platform\TestCase */
    protected $testCase;

    public function assertIdentifier($expected)
    {
        TestCase::assertEquals($expected, $this->getProp('identifier'));
    }

    public function getFieldCount()
    {
        return $this->countProp('fields');
    }

    public function getFields()
    {
        return $this->getProp('fields');
    }

    public function getFieldValues()
    {
        return collect($this->getFields())->map(function ($field) {
            return [$field['name'], $field['value'] ?? null];
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


        return static::fromArray($response->decodeResponseJson('data'));
    }
}
