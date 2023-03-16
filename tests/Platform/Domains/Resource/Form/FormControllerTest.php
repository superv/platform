<?php

namespace Tests\Platform\Domains\Resource\Form;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\Form\FormRepository;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class FormControllerTest
 *
 * @package Tests\Platform\Domains\Resource\Http\Controllers
 * @group   resource
 */
class FormControllerTest extends ResourceTestCase
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $posts;

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\FormModel
     */
    protected $formEntry;

    function test__form_page()
    {
        $form = $this->getUserPage($this->formEntry->getUrl());

        $this->assertEquals('sv-form', $form->getName());
        $this->assertEquals($this->posts->getFields()->count(), $form->countProp('fields'));

        foreach ($form->getProp('fields') as $fieldArray) {
            $field = $this->posts->getField($fieldArray['handle']);
            $this->assertEquals(
                $field->getComposer()->toForm()->get(),
                $fieldArray
            );
        }
    }

    function test__forms_are_not_accessible_by_public()
    {
        $formEntry = $this->makeForm();
        $response = $this->getJson($formEntry->getUrl());
        $response->assertStatus(401);

        $response = $this->postJson($formEntry->getUrl());
        $response->assertStatus(401);
    }

    function test__fields()
    {
        $this->withoutExceptionHandling();

        $formEntry = $this->makeForm();

        $response = $this->getJsonUser($formEntry->getUrl().'/fields/name');
        $response->assertOk();

        $fieldArray = $response->json('data');

        $field = FieldFactory::createFromArray($fieldArray);
        $this->assertEquals(
            $field->getComposer()->toForm()->get(),
            $fieldArray
        );
    }

    protected function makeForm(array $overrides = []): FormModel
    {
        $formEntry = FormRepository::resolve()->create('sv.testing', 'foo', array_merge([
            'title' => 'Public Form',
        ], $overrides));

        $formEntry->createField(['identifier' => 'sv.testing.forms.fields:name', 'type' => 'text', 'handle' => 'name']);
        $formEntry->createField(['identifier' => 'sv.testing.forms.fields:email',
                                 'type'       => 'text',
                                 'handle'     => 'email']);
        $formEntry->createField(['identifier' => 'sv.testing.forms.fields:user',
                                 'type'       => 'belongs_to',
                                 'handle'     => 'user',
                                 'config'     => [
                                     'related_resource' => 'sv.platform.users',
                                 ]]);

        return $formEntry;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->posts = $this->create('test_posts',
            function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->boolean('is_published');
            });

        /** @var FormModel $postsForm */
        $this->formEntry = FormModel::query()->where('resource_id', $this->posts->id())->first();
    }
}
