<?php

namespace Tests\Platform\Support\Meta;

use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Support\Meta\Meta;

class RepositoryTest
{
    function t1est__create()
    {
        $metaKeys = ResourceFactory::make('sv_meta_keys');
        $meta = Meta::create(['type' => 'string', 'length' => 255]);

        $this->assertNotNull($meta->uuid());
        $this->assertEquals(2, $metaKeys->count());
    }

    function t1est__update()
    {
        $metaKeys = ResourceFactory::make('sv_meta_keys');
        $meta = Meta::create(['type' => 'string', 'length' => 255]);
        $meta->set('length', 128);
        $meta->save();

        $this->assertEquals(2, $metaKeys->count());
        $lengthValue = $metaKeys->newQuery()
                                ->where('uuid', $meta->uuid())
                                ->where('key', 'length')
                                ->value('value');
        $this->assertEquals(128, $lengthValue);
    }
}