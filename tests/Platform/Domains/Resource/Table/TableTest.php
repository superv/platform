<?php

namespace Tests\Platform\Domains\Resource\Table;

use Current;
use SuperV\Platform\Domains\Resource\Table\Table;
use SuperV\Platform\Domains\Resource\Table\TableColumns;
use SuperV\Platform\Domains\Resource\Table\TableConfig;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class TableTest extends ResourceTestCase
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    /** @test */
    function builds_table_config()
    {
        $this->resource = $this->makeResource('test_users', ['name' => 'titleColumn', 'age:integer', 'bio:text']);
        $this->resource->build();

        $config = new TableConfig();
        $config->setColumns(new TableColumns($this->resource->getFields()));
        $config->build();

        $this->assertEquals(Current::url('sv/tables/'.$config->uuid()), $config->getUrl());
        $this->assertEquals(3, $config->getColumns()->count());

        $configArray = $config->compose();
        $this->assertEquals($config->getUrl(), $configArray['url']);

        $columns = collect($configArray['columns'])->keyBy('name');
        $this->assertEquals(['label' => 'Name', 'name' => 'name'], $columns->get('name'));
    }

    /** @test */
    function builds_table_rows()
    {
        $this->resource = $this->makeResource('test_users', ['name' => 'titleColumn', 'age:integer', 'bio:text']);
        $this->resource->build();

        $fakeA = $this->resource->createFake();
        $fakeB = $this->resource->createFake();
        $fakeC = $this->resource->createFake();
        $fakeD = $this->resource->createFake();

        $config = new TableConfig();
        $config->setColumns(new TableColumns($this->resource->getFields()));

        $table = app(Table::class)->setConfig($config);
        $table->setResource($this->resource);
        $table->build();

        $this->assertEquals(4, $table->getRows()->count());

        $this->assertEquals($fakeA->toArray(), $table->getRows()->get(0)->getValues());
        $this->assertEquals($fakeB->toArray(), $table->getRows()->get(1)->getValues());
        $this->assertEquals($fakeC->toArray(), $table->getRows()->get(2)->getValues());
        $this->assertEquals($fakeD->toArray(), $table->getRows()->get(3)->getValues());
    }
}