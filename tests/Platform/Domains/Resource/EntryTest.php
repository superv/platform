<?php

namespace Tests\Platform\Domains\Resource;

use SuperV\Platform\Domains\Database\Schema\Blueprint;

/**
 * Class EntryTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class EntryTest extends ResourceTestCase
{
    function test__saves_created_by_field_when_an_entry_is_created()
    {
        $this->withoutExceptionHandling();

        $posts = $this->create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->createdBy();
        });

        $this->postJsonUser($posts->route('store'), ['title' => 'Some Post'])->assertOk();

        $this->assertEquals($this->testUser->id, $posts->first()->created_by_id);
    }

    function test__saves_updated_by_field_when_an_entry_is_updated()
    {
        $this->withoutExceptionHandling();

        $posts = $this->create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->updatedBy();
        });

        $post = $posts->create(['title' => 'Some Post']);

        $this->postJsonUser($post->route('update'), ['title' => 'Updated Post'])->assertOk();

        $this->assertEquals($this->testUser->id, $posts->first()->updated_by_id);
    }
}