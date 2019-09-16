<?php

namespace SuperV\Platform\Domains\Resource\Testing;

use Closure;
use Current;
use Illuminate\Support\Str;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\ResourceModel;
use SuperV\Platform\Testing\HelperComponent;
use Tests\Platform\Domains\Resource\Fixtures\Blueprints;

trait ResourceTestHelpers
{
    protected function getUserPage($url)
    {
        $response = $this->getJsonUser($url);
        $response->assertOk();

        return HelperComponent::from($response->decodeResponseJson('data'));
    }

    protected function getPublicPage($url)
    {
        $response = $this->getJson($url);
        $response->assertOk();

        return HelperComponent::from($response->decodeResponseJson('data'));
    }

    protected function blueprints()
    {
        return new Blueprints;
    }

    protected function anyTable()
    {
        $res = $this->create('any_table', function (Blueprint $table, ResourceConfig $config) {
            $config->label('Any Resource');
            $config->setNamespace('testing');
            $table->increments('id');

            $table->string('title')->entryLabel();
        });

        return $res;
    }

    /**
     * @param               $table
     * @param \Closure|null $callback
     * @return \SuperV\Platform\Domains\Resource\Resource
     */
    protected function create($table, Closure $callback = null, $connection = null)
    {
        if (str_contains($table, '::')) {
            list($namespace, $table) = explode('::', $table);
        } else {
            $namespace = 'platform';
        }

        Current::setMigrationScope($namespace);

        if ($connection) {
            $config = Schema::connection($connection)->create($table, $callback);
        } else {
            $config = Schema::create($table, $callback);
        }

        return ResourceFactory::make($config->getIdentifier());
    }

    /**
     * @param \Closure|null $callback
     * @return \SuperV\Platform\Domains\Resource\Resource
     */
    protected function randomTable(Closure $callback = null)
    {
        $config = Schema::create($table = Str::random(8), $callback);

        return ResourceFactory::make($config->getIdentifier());
    }

    /** @return \SuperV\Platform\Domains\Resource\Resource */
    protected function makeResource($table, array $columns = ['name'], array $resource = [])
    {
        if (str_contains($table, '::')) {
            list($namespace, $table) = explode('::', $table);
        }
        $identifier = ($namespace ?? 'platform').'::'.$table;
        $this->makeResourceModel($identifier, $columns, $resource);

        return ResourceFactory::make($identifier);
    }

    /** @return \SuperV\Platform\Domains\Resource\ResourceModel */
    protected function makeResourceModel($table, array $columns, array $resource = [])
    {
        if (str_contains($table, '::')) {
            list($namespace, $table) = explode('::', $table);
        } else {
            $namespace = 'platform';
        }
        Schema::create($table,
            function (Blueprint $table, ResourceConfig $config) use (
                $columns,
                $resource,
                $namespace
            ) {
                $config->setNamespace($namespace);
                $table->increments('id');

                foreach ($columns as $key => $column) {
                    $parameters = [];
                    if (is_string($key)) {
                        $parameters = explode('|', $column);
                        $column = $key;
                    }

                    if ($column === 'timestamps') {
                        $table->timestamps();
                        continue;
                    }
                    $parts = explode(':', $column);
                    $type = count($parts) === 1 ? 'string' : $parts[1];
                    $name = $parts[0];

                    $column = $table->addColumn($type, $name);
                    foreach ($parameters as $param) {
                        $column->{$param}();
                    }
                }

                $config->fill($resource);
            });
        $resource = ResourceModel::withIdentifier($namespace.'::'.$table);

        return $resource;
    }

    protected function getTableConfigOfResource($resource)
    {
        $response = $this->getJsonUser($resource->route('dashboard', null, ['section' => 'table']));
        $table = HelperComponent::from($response->decodeResponseJson('data'));

        return $table;
    }

    protected function getTableRowsOfResource($resource, $query = '')
    {
        $url = $resource->route('dashboard', null, ['section' => 'table']).'/data'.str_prefix($query, '?', '');
        $response = $this->getJsonUser($url)->assertOk();

        return $response->decodeResponseJson('data.rows');
    }

    protected function creatFieldEntry() { }
}
