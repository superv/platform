<?php

namespace SuperV\Platform\Testing;

class FormComponent extends HelperComponent
{
    public function getFieldCount()
    {
        return $this->countProp('fields');
    }

    public static function get($identifier, $testCase)
    {
        if (class_exists($identifier)) {
            $identifier = $identifier::$identifier;
        }

        $url = sv_route('sv::forms.show', ['identifier' => $identifier]);

        $response = $testCase->getJsonUser($url)->assertOk();

        return FormComponent::from($response->decodeResponseJson('data'));
    }
}
