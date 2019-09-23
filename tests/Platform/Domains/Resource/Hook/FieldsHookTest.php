<?php

namespace Tests\Platform\Domains\Resource\Hook;

class FieldsHookTest extends HookTestCase
{
    function test__single_hook_file()
    {
        $_SERVER['__hooks::fields.title.resolved'] = null;

        $posts = $this->blueprints()->posts();

        $this->assertEquals($posts->getField('title')->getIdentifier(), $_SERVER['__hooks::fields.title.resolved']);
    }
}
