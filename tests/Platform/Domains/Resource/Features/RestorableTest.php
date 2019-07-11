<?php

namespace Tests\Platform\Platform\Domains\Resource\Features;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class RestorableTest
 *
 * @package Tests\Platform\Platform\Domains\Resource\Features
 * @group   resource
 */
class RestorableTest extends ResourceTestCase
{
    function test__creates_table_columns_and_sets_config()
    {
        $entries = $this->createEntriesResource();

        $this->assertTrue($entries->isRestorable());
        $this->assertColumnExists('t_entries', 'deleted_at');
        $this->assertColumnExists('t_entries', 'deleted_by_id');
    }

    function test__soft_deletes_entry_when_enabled()
    {
        $entries = $this->createEntriesResource();

        $entry = $entries->create([]);
        $this->assertNull($entry->deleted_at);
        $this->assertNull($entry->deleted_by_id);

        $this->deleteJsonUser($entry->route('delete'))->assertOk();

        $trashed = $entry->fresh();
        $this->assertNotNull($trashed->deleted_at);
        $this->assertNotNull($trashed->deleted_by_id);
        $this->assertEquals(0, $entries->count());
    }

    function test__restore()
    {
        $entries = $this->createEntriesResource();

        $entry = $entries->create([]);
        $this->assertNull($entry->deleted_at);
        $entry->delete();
        $this->assertEquals(0, $entries->count());
        $this->assertNotNull($entry->fresh()->deleted_at);

        $entry->restore();
        $this->assertNull($entry->fresh()->deleted_at);
        $this->assertNull($entry->fresh()->deleted_by_id);
        $this->assertEquals(1, $entries->count());
    }

    /**
     * @group http
     */
    function test__restore_over_http()
    {
        $this->withoutExceptionHandling();

        // ARRANGE
        $entries = $this->createEntriesResource();
        $entry = $entries->create([]);

        $this->deleteJsonUser($entry->route('delete'))->assertOk();

        // ACT
        $response = $this->postJsonUser($entry->route('restore'));
        $response->assertOk();

        // ASSERT
        $restored = $entry->fresh();
        $this->assertNull($restored->deleted_at);
        $this->assertNull($restored->deleted_by_id);
    }

    function test__force_delete()
    {
        $entries = $this->createEntriesResource();

        $entry = $entries->create([]);
        $this->assertNull($entry->deleted_at);
        $entry->delete();
        $this->assertNotNull($entry->fresh()->deleted_at);

        $entry->forceDelete();
        $this->assertNull($entry->fresh());
    }

    function test__nothing_is_broken_with_unrestorable_models()
    {
        $items = $this->create('t_items', function (Blueprint $table) {
            $table->increments('id');
        });

        $item = $items->create([]);
        $item->delete();
        $this->assertNull($item->fresh());
    }

    /**
     * @return \SuperV\Platform\Domains\Resource\Resource
     */
    protected function createEntriesResource(): \SuperV\Platform\Domains\Resource\Resource
    {
        $entries = $this->create('t_entries', function (Blueprint $table) {
            $table->increments('id');
            $table->restorable();
        });

        return $entries;
    }
}