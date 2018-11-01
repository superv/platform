<?php

namespace Tests\Platform\Domains\Resource;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Database\Blueprint;
use SuperV\Platform\Domains\Database\Schema;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\ResourceModel;

class ResourceTestCase extends \Tests\Platform\TestCase
{
    use RefreshDatabase;

    protected function makeResource($slug = 'test_users', array $columns = ['name']): Resource
    {
        $this->makeResourceModel($slug, $columns);

        return ResourceFactory::make($slug);
    }

    protected function makeResourceModel($slug, array $columns): ResourceModel
    {
        Schema::create($slug, function (Blueprint $table) use ($columns) {
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
        });
        $resource = ResourceModel::withSlug($slug);

        return $resource;
    }
}