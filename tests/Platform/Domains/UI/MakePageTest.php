<?php

namespace Tests\Platform\Domains\UI;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Testing\ResourceTestHelpers;
use SuperV\Platform\Domains\UI\Page\MakePage;
use Tests\Platform\Domains\Resource\Fixtures\HelperComponent;
use Tests\Platform\TestCase;

class MakePageTest extends TestCase
{
    use RefreshDatabase;
    use ResourceTestHelpers;

    protected $flags = [];

    function test__bsmllh()
    {
        $this->withoutExceptionHandling();
        $fields = [
            FieldFactory::createFromArray(['type' => 'text', 'name' => 'title']),
            FieldFactory::createFromArray(['type' => 'text', 'name' => 'email', 'flags' => ['nullable']]),
        ];

        $page = MakePage::forUrl('sv/pages/abc')
                        ->setFields($fields)
                        ->onSuccess(function (Form $form) {
                            $this->assertEquals('Users', $form->getFieldValue('title'));

                            return ['status' => 'ok'];
                        })
                        ->make();

        $page->setMeta('title', 'abc index');
        $page->build();

        $page = $this->getPageFromUrl('sv/pages/abc');
        $this->assertNotNull($page);
        $this->assertEquals('abc index', $page->getProp('meta.title'));
        $form = HelperComponent::from($page->getProp('blocks.0'));
        $this->assertEquals(count($fields), count($form->getProp('fields')));

        $response = $this->postJsonUser('sv/pages/abc', ['title' => 'Users', 'email' => 'a'])->assertOk();
        $this->assertEquals('ok', $response->decodeResponseJson('status'));
    }
}