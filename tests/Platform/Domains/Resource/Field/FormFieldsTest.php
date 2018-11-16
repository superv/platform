<?php

namespace Tests\Platform\Domains\Resource\Field;

use Storage;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\Testing\ResourceFormTester;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class FormFieldsTest
 * BelongsTo
 * Boolean *
 * Datetime
 * Email
 * File *
 * Number *
 * Select
 * Text *
 * Textarea
 */
class FormFieldsTest extends ResourceTestCase
{
    /** @var ResourceFormTester */
    protected $formTester;

    protected function setUp()
    {
        parent::setUp();

        $groups = $this->create('t_groups',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
            });

        $groups->fake([], 4);

        $users = $this->create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->entryLabel();
            $table->unsignedInteger('age');
            $table->file('avatar')->config(['disk' => 'fakedisk']);
            $table->belongsTo('t_groups', 'group');
        });

        //upload
        Storage::fake('fakedisk');

        $this->formTester = new ResourceFormTester($this->basePath(), $users);
    }

    function test__create_form_with_all_field_types()
    {
        $this->withoutExceptionHandling();


        $this->formTester->testCreate();
    }

    function test__update_form_with_all_field_types()
    {
        $this->withoutExceptionHandling();

        $this->formTester->testUpdate();
    }
}


