<?php

namespace Tests\Platform\Domains\Resource\Field\Types\RelatesToOne;

use SuperV\Platform\Domains\Resource\Builder\Blueprint;
use SuperV\Platform\Domains\Resource\Builder\Builder;
use SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne\Composer;
use SuperV\Platform\Domains\Resource\Form\FormFactory;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class ComposerTest extends ResourceTestCase
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $addresses;

    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $students;

    /**
     * @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract
     */
    protected $addressEntry;

    /**
     * @var array|\SuperV\Platform\Domains\Resource\Database\Entry\ResourceEntry
     */
    protected $studentEntry;

    function test__create_form()
    {
        $field = $this->students->getField('address');
        $field->setConfigValue('meta.options', ['options-array']);
        $payload = $field->getComposer()->toForm();

        $this->assertEquals(['options-array'], $payload->get('meta.options'));

        $placeholder = __('Select :Object', [
            'object' => $field->type()->getRelated()->getSingularLabel(),
        ]);
        $this->assertEquals($placeholder, $payload->get('placeholder'));
    }

    function test__update_form()
    {
        $form = $this->makePartialMock(FormFactory::builderFromResource($this->students)->getForm());

        $form->expects('getFieldRpcUrl')->with('address', 'options')->andReturn('rpc-url');
        $field = $this->students->getField('address');
        $payload = $field->getComposer()->toForm($form);

        $this->assertEquals('rpc-url', $payload->get('meta.options'));
    }

    function test__view()
    {
        $this->be($this->newUser());
        $composer = $this->students->getField('address')->getComposer();

        $this->assertEquals($this->addressEntry->router()->dashboardSPA(), $composer->toView($this->studentEntry)->get('meta.link'));
    }

    function test__meta_link_should_be_null_if_logged_in_user_is_not_authorized_to_view_the_related_entry()
    {
        $this->be($this->newUser(['allow' => null]));
        $composer = $this->students->getField('address')->getComposer();

        $this->assertNull($composer->toView($this->studentEntry)->get('meta.link'));
    }

    function test__table()
    {
        $composer = $this->students->getField('address')->getComposer();

        $this->assertEquals('my address', $composer->toTable($this->studentEntry)->get('value'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->students = Builder::create('tst.students', function (Blueprint $resource) {
            $resource->text('name', 'Student Name')->useAsEntryLabel();
            $resource->relatesToOne('tst.addresses', 'address')
                     ->showOnLists();
        });
        $this->addresses = Builder::create('tst.addresses', function (Blueprint $resource) {
            $resource->primaryKey('id')
                     ->number();
            $resource->text('title', 'Address Title')->useAsEntryLabel();
        });

        $this->addressEntry = $this->addresses->create(['title' => 'my address']);
        $this->studentEntry = $this->students->fake(['address_id' => $this->addressEntry->getId()]);
    }

    protected function makeComposer(): \SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne\Composer
    {
        $field = $this->students->getField('address');
        $composer = new Composer();
        $composer->setField($field);

        return $field->getComposer();

        return $composer;
    }
}