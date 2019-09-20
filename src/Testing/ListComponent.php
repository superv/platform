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
        TestCase::assertEquals($url, $this->getDataUrl());
    }

    public function getData()
    {
        $response = $this->testCase->getJsonUser($this->getDataUrl());

        return new ListData($response->decodeResponseJson('data'), $this->testCase);
    }

    public static function get(Resource $resource, TestCase $testCase)
    {
        $response = $testCase->getJsonUser($resource->router()->defaultList());

        if (! $response->isOk()) {
            dd($response->content());
        }

        $list = static::from($response->decodeResponseJson('data'));
        $list->testCase = $testCase;

        return $list;
    }

    protected function getDataUrl()
    {
        return $this->getProp('config.data_url');
    }
}

class ListData
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var \Tests\Platform\TestCase
     */
    protected $testCase;

    public function __construct(array $data = [], TestCase $testCase)
    {
        $this->data = $data;
        $this->testCase = $testCase;
    }

    public function rowCount()
    {
        return count($this->rows());
    }

    public function rows()
    {
        return $this->data['rows'];
    }
}
