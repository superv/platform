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

        $url = sv_route('sv::forms.display', ['form' => $identifier]);

        $response = $testCase->getJsonUser($url);
        if (! $response->isOk()) {
            dd($response->decodeResponseJson());
        }


        return static::fromArray($response->decodeResponseJson('data'));
    }
}
