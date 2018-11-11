<?php

namespace Tests\Platform\Support\Meta;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\Testing\ResourceTestHelpers;
use SuperV\Platform\Support\Meta\Meta;
use SuperV\Platform\Support\Meta\Repository;
use Tests\Platform\TestCase;

class RepositoryTest extends TestCase
{
    use RefreshDatabase;
    use ResourceTestHelpers;

    function test__creates_db_record_single_level()
    {
        $meta = new Meta(['name' => 'Omar', 'age' => 33]);
        ($repo = new Repository)->save($meta);

        $records = $repo->all();
        $this->assertEquals(2, $records->count());

        $this->assertArrayContains([
            'uuid' => $meta->uuid(),
            'value' => 'Omar'
        ], $records->where('key', 'name')->first()->toArray());

        $this->assertArrayContains([
            'uuid' => $meta->uuid(),
            'value' => 33
        ], $records->where('key', 'age')->first()->toArray());
    }

    function test__creates_db_record_2_level()
    {
        $meta = new Meta(['config' => ['rules' => ['min' => 10, 'max' => 99]]]);
        ($repo = new Repository)->save($meta);

        $records = $repo->all();
        $this->assertEquals(4, $records->count());
    }

    function test_load() {
        $meta = new Meta(['config' => ['rules' => ['min' => 10, 'max' => 99]]]);
        ($repo = new Repository)->save($meta);

        $fresh = $repo->load($meta->uuid());
        $this->assertEquals($meta->compose(), $fresh->compose());
    }

    function test_owner() {

    }
}