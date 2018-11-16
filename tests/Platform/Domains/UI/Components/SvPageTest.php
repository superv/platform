<?php

namespace Tests\Platform\Domains\UI\Components;

use SuperV\Platform\Domains\UI\Components\SvPage;
use Tests\Platform\TestCase;

class SvPageTest extends TestCase
{
    function test__constructor()
    {
        $page = SvPage::make('sv_users_index', 'Users Index');
        $page->addBlock('block a');
        $page->addBlock('block b');

        $this->assertNotNull($page->uuid());
        $this->assertEquals('sv_users_index', $page->getName());
        $this->assertEquals('Users Index', $page->getTitle());
        $this->assertEquals(['block a', 'block b'], $page->getBlocks());
    }

    function test__compose()
    {
        $this->fail('Over here...');
    }
}