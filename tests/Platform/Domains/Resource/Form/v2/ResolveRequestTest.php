<?php

namespace Tests\Platform\Domains\Resource\Form\v2;

use Mockery;
use SuperV\Platform\Domains\Resource\Form\v2\EntryRepositoryInterface;
use SuperV\Platform\Domains\Resource\Form\v2\FormFactory;
use SuperV\Platform\Domains\Resource\Form\v2\Jobs\ResolveRequest;
use SuperV\Platform\Domains\Resource\Model\AnonymousModel;
use Tests\Platform\Domains\Resource\Form\v2\Helpers\FormFake;
use Tests\Platform\Domains\Resource\Form\v2\Helpers\FormTestHelpers;

class ResolveRequestTest
{
    use FormTestHelpers;

    function test__resolves_GET_request()
    {
        $request = $this->makeGetRequest(['entries' => ['ab.foo.1', 'xy.bar.2']]);

        $form = FormFake::fake()->setFakeFields([
            'ab.foo.fields:title',
            'xy.bar.fields:email',
        ]);

        $fooEntry = Mockery::mock((new AnonymousModel(['title' => 'foo-title'])))->makePartial();
        $barEntry = Mockery::mock((new AnonymousModel(['email' => 'bar-title'])))->makePartial();

        $repository = $this->bindMock(EntryRepositoryInterface::class);

        $repository->shouldReceive('getEntry')->with('ab.foo', 1)->andReturn($fooEntry)->once();
        $repository->shouldReceive('getEntry')->with('xy.bar', 2)->andReturn($barEntry)->once();

//        $form->handle($request);

        ResolveRequest::resolve()->handle($form, $request);

        $this->assertEquals(['ab.foo' => 1, 'xy.bar' => 2], $form->getEntryIds());
        $this->assertEquals('foo-title', $form->getFieldValue('ab.foo.fields:title'));
    }

    function test__resolves_POST_request()
    {
        $request = $this->makePostRequest(
            ['entries' => ['ab.foo.1', 'xy.bar.2']],
            [
                'ab.foo.fields:title' => 'updated-foo-title',
                'xy.bar.fields:email' => 'updated-bar-email',
            ]);

        $formEntry = FormFake::fake()->setFakeFields([
            'ab.foo.fields:title',
            'xy.bar.fields:email',
        ])->createFormEntry();

        $form = FormFactory::createBuilder($formEntry)->getForm();

        $fooEntry = Mockery::mock((new AnonymousModel(['title' => 'foo-title'])))->makePartial();
        $fooEntry->shouldReceive('setAttribute')->with('title', 'updated-foo-title')->once();

        $barEntry = Mockery::mock((new AnonymousModel(['email' => 'bar-title'])))->makePartial();
        $barEntry->shouldReceive('setAttribute')->with('email', 'updated-bar-email')->once();

        $fooEntry->shouldReceive('save')->once();
        $barEntry->shouldReceive('save')->once();

        $repository = $this->bindMock(EntryRepositoryInterface::class);
        $repository->shouldReceive('getEntry')->with('ab.foo', 1)->andReturn($fooEntry)->once();
        $repository->shouldReceive('getEntry')->with('xy.bar', 2)->andReturn($barEntry)->once();

        ResolveRequest::resolve()->handle($form, $request);
        $fooEntry->shouldHaveReceived('save')->once();
        $barEntry->shouldHaveReceived('save')->once();
    }
}

