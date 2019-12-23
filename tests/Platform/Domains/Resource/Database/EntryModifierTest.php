<?php

namespace Tests\Platform\Domains\Resource\Database;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class EntryModifierTest extends ResourceTestCase
{
    function test__casts()
    {
        $items = $this->create('tbl_any', function (Blueprint $table) {
            $table->id();
            $table->boolean('active');
//            $table->dictionary('config');
        });

        $entry = $items->create(['active' => 'true']);

        $this->assertSame(true, $entry->active);
    }
}