<?php

namespace Tests\Platform\Domains\Resource;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Database\Blueprint\Blueprint;
use SuperV\Platform\Domains\Database\Schema;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceBlueprint;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\ResourceModel;

class ResourceTestCase extends \Tests\Platform\TestCase
{
    use RefreshDatabase;

    protected function makeResource($slug = 'test_users', array $columns = ['name'], array $resource = []): Resource
    {
        $this->makeResourceModel($slug, $columns, $resource);

        return ResourceFactory::make($slug);
    }

    protected function makeResourceModel($slug, array $columns, array $resource): ResourceModel
    {

        Schema::create($slug, function (Blueprint $table, ResourceBlueprint $resourceBlueprint) use ($columns, $resource) {
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

            $resourceBlueprint->fill($resource);
        });
        $resource = ResourceModel::withSlug($slug);

        return $resource;
    }
}