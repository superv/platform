<?php

namespace SuperV\Platform\Testing;

use SuperV\Platform\Domains\Auth\Contracts\User;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class ListComponent extends HelperComponent
{
    /** @var PlatformTestCase */
    protected $testCase;

    protected $user;

    /** @var \Illuminate\Support\Collection */
    protected $fields;

    /** @var \Illuminate\Support\Collection */
    protected $rowActions;

    public function assertDataUrl($url)
    {
        PlatformTestCase::assertEquals($url, $this->getDataUrl());
    }

    public function getFieldCount()
    {
        return $this->getFields()->count();
    }

    public function getFields()
    {
        if (! $this->fields) {
            $this->fields = collect($this->getProp('config.fields'))
                ->keyBy(function ($field) {
                    return $field['handle'];
                });
        }

        return $this->fields;
    }

    public function getRowActions()
    {
        if (! $this->rowActions) {
            $this->rowActions = collect($this->getProp('config.row_actions'))
                ->map(function($action) {
                    return HelperComponent::fromArray($action);
                })
                ->keyBy(function (HelperComponent $action) {
                    return $action->getProp('name');
                });
        }

        return $this->rowActions;
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
    public function getRowAction($name )
    {
        return $this->getRowActions()->get($name);
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
