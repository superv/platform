<?php

namespace Tests\Platform\Domains\Resource\Field\Types\RelatesToOne;

use SuperV\Platform\Domains\Resource\Builder\Blueprint;
use SuperV\Platform\Domains\Resource\Builder\Builder;
use SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne\Composer;
use SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne\RelatesToOneType;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class ComposerTest extends ResourceTestCase
{
    function test__view()
    {
        $relatedEntry = $this->makeEntryMock();
        $relatedEntry->expects('getEntryLabel')->andReturn('address-title');

        $fieldTypeMock = $this->makePartialMock(RelatesToOneType::class);
        $fieldTypeMock->expects('getRelatedEntry')
                      ->with($parentEntry = $this->makeEntryMock())
                      ->andReturn($relatedEntry);

        $composer = new Composer();
        $composer->setField($this->makeField('address', $fieldTypeMock));

        $this->assertEquals('address-title', $composer->toView($parentEntry)->get('value'));
    }

    function test__table()
    {
        $addresses = sv_resource('tst.addresses');

        $students = sv_resource('tst.students');
        $student = $students->fake(['address_id' => $addresses->create(['title' => 'my address'])->getId()]);

        $field = $students->getField('address');
        $composer = new Composer();
        $composer->setField($field);

        $this->assertEquals('my address', $composer->toTable($student)->get('value'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        Builder::create('tst.students', function (Blueprint $resource) {
            $resource->text('name', 'Student Name')->useAsEntryLabel();
            $resource->relatesToOne('tst.addresses', 'address')
                     ->withLocalKey('address_id')
                     ->showOnLists();
        });

        Builder::create('tst.addresses', function (Blueprint $resource) {
            $resource->primaryKey('id')
                     ->number();
            $resource->text('title', 'Address Title')->useAsEntryLabel();
        });
    }
//    function  __view()
//    {
//        $parentEntry = $this->makeEntryMock('tst.students', ['address_id' => 5]);
//
//        $field = $this->makeRelatesToOneField([
//            'related' => 'tst.addresses',
//            'local_key' => $localKey = 'address_id',
//            'owner_key' => $ownerKey = 'aid',
//        ]);
//
//
//        $entryRepoMock = $this->bindMock(EntryRepositoryInterface::class);
//        $entryRepoMock->expects('setResource')->with('tst.addresses')->andReturnSelf();
//        $entryRepoMock->expects('newQuery')->andReturn($queryMock = $this->makeMock(\Illuminate\Database\Eloquent\Builder::class));
//
//        $repoMock = $this->bindMock(Repository::class);
//        $repoMock->expects('setField')->with($field)->andReturnSelf();
//
//
//
//        $queryMock->expects('where')->with($ownerKey, $parentEntry->getAttribute($localKey))->andReturnSelf();
//        $queryMock->expects('first')->andReturn($relatedEntry = $this->makeEntryMock());
//        $relatedEntry->expects('getEntryLabel')->andReturn('entry-label');
//
//
//        $composer = $field->getComposer();
//        $this->assertInstanceOf(Composer::class, $composer);
//
//        $this->assertEquals('entry-label', $composer->toView($parentEntry)->get('value'));
//
//    }
}