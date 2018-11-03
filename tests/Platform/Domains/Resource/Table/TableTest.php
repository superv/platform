<?php

namespace Tests\Platform\Domains\Resource\Table;

use SuperV\Platform\Domains\Resource\Action\Action;
use SuperV\Platform\Domains\Resource\Resource;
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
        $config->setResource($this->resource);
//        $config->setUrl(sv_url($this->resource->route('table')));
        $config->setColumns(new TableColumns($this->resource->getFields()));
        $config->build();

        $this->assertEquals(sv_url($this->resource->route('table.data', ['uuid' => $config->uuid()])), $config->getUrl());
        $this->assertEquals(3, $config->getColumns()->count());

        $configArray = $config->compose();
        $this->assertEquals($config->getUrl(), array_get($configArray, 'config.dataUrl'));

        $columns = collect(array_get($configArray, 'config.meta.columns'))->keyBy('name');
        $this->assertEquals(['label' => 'Name', 'name' => 'name'], $columns->get('name'));
    }

    /** @test */
    function builds_table_rows()
    {
        $this->resource = $this->makeResource('test_users', ['name' => 'titleColumn', 'age:integer', 'bio:text']);
        $this->resource->build();

        [$fakeA, $fakeB, $fakeC, $fakeD] = $this->resource->createFake([], 4);

        $config = new TableConfig();

        $config->setResource($this->resource);
        $config->setColumns(new TableColumns($this->resource->getFields()));
        $config->setActions([Action::make('edit'), Action::make('delete')]);

        $table = Table::config($config)
                      ->setResource($this->resource)
                      ->build();

        $this->assertTrue($config->isBuilt());
        $this->assertTrue($table->isBuilt());

        $this->assertEquals(4, $table->getRows()->count());
        $this->assertEquals($fakeA->toArray(), $table->getRows()->get(0)->getValues());
        $this->assertEquals($fakeB->toArray(), $table->getRows()->get(1)->getValues());
        $this->assertEquals($fakeC->toArray(), $table->getRows()->get(2)->getValues());
        $this->assertEquals($fakeD->toArray(), $table->getRows()->get(3)->getValues());

        $rowResource = Resource::of($fakeA);
        $rowActions = $table->getRows()->first()->getActions();
        $this->assertEquals([
            ['name' => 'edit', 'title' => 'Edit', 'url' => $rowResource->route('edit')],
            ['name' => 'delete', 'title' => 'Delete', 'url' => $rowResource->route('delete')],
        ], $rowActions);

        $this->withoutExceptionHandling();

        $configArray = $config->compose();

        $this->newUser();
        $response = $this->getJson($this->resource->route('table'), $this->getHeaderWithAccessToken());
        $this->assertEquals(array_get($configArray, 'config.meta.columns'), $response->decodeResponseJson('data.props.block.props.config.meta.columns'));

        $response = $this->getJson($this->resource->route('table.data', ['uuid' => $config->uuid()]), $this->getHeaderWithAccessToken());
        $this->assertEquals($table->compose(), $response->decodeResponseJson('data'));
    }
}