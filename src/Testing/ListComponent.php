<?php

namespace SuperV\Platform\Testing;

use SuperV\Platform\Domains\Auth\Contracts\User;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class ListComponent extends HelperComponent
{
    /** @var PlatformTestCase */
    protected $testCase;

    protected $user;

    public function assertDataUrl($url)
    {
        PlatformTestCase::assertEquals($url, $this->getDataUrl());
    }

    public function getData()
    {
//        Config::set('app.debug', true);
        $response = $this->testCase->getJsonUser($this->getDataUrl(), $this->user);

//        dd($response->decodeResponseJson());

        return new ListData($response->decodeResponseJson('data'), $this->testCase);
    }

    public static function get($resource, PlatformTestCase $testCase, User $user = null)
    {
        if (is_string($resource)) {
            $resource = ResourceFactory::make($resource);
        }

        $response = $testCase->getJsonUser($resource->router()->defaultList(), $user);

        if (! $response->isOk()) {
            dd($response->content());
        }

        $list = static::fromArray($response->decodeResponseJson('data'));
        $list->testCase = $testCase;
        $list->user = $user;

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

    public function __construct(array $data = [], $testCase)
    {
        $this->data = $data;
        $this->testCase = $testCase;
    }

    public function rowCount()
    {
        return $this->rows()->count();
    }

    public function rows()
    {
        return collect($this->data['rows']);
    }
}
