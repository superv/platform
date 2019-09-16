<?php

namespace Tests\Platform\Domains\Resource\Form\v2;

use SuperV\Platform\Domains\Resource\Form\FormField;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FieldComposer;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\Form;
use SuperV\Platform\Domains\Resource\Form\v2\FormFieldCollection;
use SuperV\Platform\Support\Composer\Payload;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class ComposeFormTest extends ResourceTestCase
{
    use FormTestHelpers;

    function test__compose()
    {
        $this->app->bind(Form::class, FormFake::class);
        $fieldComposerMock = $this->bindMock(FieldComposer::class);

        /** @var Form $form */
        $form = app(Form::class);

        $form->getFields()->map(function (FormField $field) use ($form, $fieldComposerMock) {
            $fieldComposerMock->shouldReceive('toForm')
                              ->withArgs(function (Form $formArg, FormField $fieldArg) use ($form, $field) {
                                  return $fieldArg->getIdentifier() === $field->getIdentifier()
                                      && $formArg->getIdentifier() === $form->getIdentifier();
                              })
                              ->andReturn(['composed-'.$field->getIdentifier()])
                              ->once();
        });

        $payload = $form->compose();

        $this->assertInstanceOf(Payload::class, $payload);

        $this->assertEquals([
            'identifier' => 'form-id',
            'url'        => 'url-to-form',
            'method'     => 'PATCH',
            'fields'     => [
                ['composed-field-1'],
                ['composed-field-2'],
            ],
        ], $payload->get());
    }
}


class FormFake extends \SuperV\Platform\Domains\Resource\Form\v2\Form
{
    protected $identifier = 'form-id';

    protected $url = 'url-to-form';

    protected $method = 'PATCH';

    public function getFields(): FormFieldCollection
    {
        return FormFieldCollection::make([
            'field-1' => FormFieldFake::fake('field-1'),
            'field-2' => FormFieldFake::fake('field-2'),
        ]);
    }
}
