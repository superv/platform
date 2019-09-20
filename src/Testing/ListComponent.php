<?php

namespace SuperV\Platform\Testing;

use SuperV\Platform\Domains\Resource\Resource;
use Tests\Platform\TestCase;

class ListComponent extends HelperComponent
{
    /** @var \Tests\Platform\TestCase */
    protected $testCase;

    public function assertDataUrl($url)
    {
        $this->testCase::assertEquals($url, $this->getProp('config.data_url'));
    }

    public static function get(Resource $resource, TestCase $testCase)
    {
        $response = $testCase->getJsonUser($resource->router()->defaultList())->assertOk();

        $list = static::from($response->decodeResponseJson('data'));
        $list->testCase = $testCase;

        return $list;
    }
}
