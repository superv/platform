<?php

namespace Tests\Platform\Domains\Resource\Hook;

use SuperV\Platform\Domains\Resource\Form\Contracts\Form;
use SuperV\Platform\Domains\Resource\Hook\Hook;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class FormHookTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class FormHookTest extends ResourceTestCase
{
    function test_resolved()
    {
        $this->withoutExceptionHandling();

        $_SERVER['__hooks::forms.default.resolved'] = null;
        $resource = $this->blueprints()->orders();

        $response = $this->getJsonUser($resource->router()->createForm());
        $response->assertOk();

        $this->assertNotNull($_SERVER['__hooks::forms.default.resolved']);

        $form = $_SERVER['__hooks::forms.default.resolved']['form'];
        $this->assertInstanceOf(Form::class, $form);
    }

    protected function setUp()
    {
        parent::setUp();

        Hook::resolve()->scan(__DIR__.'/../Fixtures/Resources');
    }
}
