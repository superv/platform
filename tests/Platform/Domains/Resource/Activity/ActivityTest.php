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
        $action = $this->blueprints()->actions()->first();

        $this->getJsonUser($action->router()->view())->assertOk();

        $log = sv_resource('platform.activities')->first();
        $this->assertEquals('sv::entry.view', $log->activity);
        $this->assertEquals($this->testUser->getId(), $log->user_id);
        $this->assertEquals($action->getId(), $log->entry_id);
        $this->assertEquals($action->getResourceIdentifier(), $log->entry_type);
    }
}
