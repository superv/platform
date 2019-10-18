<?php

namespace Tests\Platform\Domains\Resource\Hook;

class HookByRoleTest extends HookTestCase
{
    function test__normal_user()
    {
        $this->blueprints()->users();

        $this->testUser = $this->newUser(['name' => 'only user']);
        $this->be($this->testUser);

        $_SERVER['__hooks::form.default.resolving'] = null;
        $_SERVER['__hooks::resource.resolved'] = null;
        $posts = $this->blueprints()->posts();
        $this->assertEquals($posts->getIdentifier(), $_SERVER['__hooks::resource.resolved']);

        $this->getFormComponent($posts);
        $this->assertEquals('PostsForm', $_SERVER['__hooks::form.default.resolving']);
    }

    function test__manager_user()
    {
        $this->blueprints()->users();

        $this->testUser = $this->newUser(['name' => 'only user']);
        $this->testUser->assign('manager');
        $this->be($this->testUser);

        $_SERVER['__hooks::form.default.resolving'] = null;
        $posts = $this->blueprints()->posts();
        $this->assertEquals($posts->getIdentifier().'.role:manager', $_SERVER['__hooks::resource.manager.resolved']);

        $this->getFormComponent($posts);
        $this->assertEquals('PostsForm.PostsManagerForm', $_SERVER['__hooks::form.default.resolving']);
    }
}