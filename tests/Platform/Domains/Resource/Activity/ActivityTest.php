<?php

namespace Tests\Platform\Domains\Resource\Activity;

use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class ActivityTest
 *
 * @package Tests\Platform\Domains\Resource\Activity
 * @group   resource
 */
class ActivityTest extends ResourceTestCase
{
    function test__view_resource()
    {
        $this->setUpPort('acp', 'localhost');
        $this->withoutExceptionHandling();
        $action = $this->schema()->actions()->first();

        $this->getJsonUser($action->route('view'))->assertOk();

        $log = sv_resource('sv_activities')->first();
        $this->assertEquals('resource.view', $log->activity);
        $this->assertEquals($this->testUser->getId(), $log->user_id);
        $this->assertEquals($action->getId(), $log->entry_id);
        $this->assertEquals($action->getTable(), $log->entry_type);
    }
}