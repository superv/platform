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

    public static function get($identifier, TestCase $testCase)
    {
        if (class_exists($identifier)) {
            $identifier = $identifier::$identifier;
        }

        $url = sv_route('sv::forms.show', ['identifier' => $identifier]);

        $response = $testCase->getJsonUser($url)->assertOk();

        return static::from($response->decodeResponseJson('data'));
    }
}
