<?php

namespace Tests\Platform\Domains\Resource\Form\Features;

use SuperV\Platform\Domains\Resource\Field\Types\BelongsTo\BelongsToField;
use SuperV\Platform\Domains\Resource\Form\FormFactory;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class InlineFormTest extends ResourceTestCase
{
    function test__add_static_flag_to_field_from_migrations()
    {
        $form = FormFactory::builderFromResource($this->blueprints()->clients())
                           ->getForm();

        $userField = $form->fields()->get('user');
        $this->assertTrue($userField->hasFlag('static'));

        $form->resolve();

        $this->assertTrue($userField->isHidden());

        $subFormField = $form->fields()->get('user');
        $this->assertInstanceOf(BelongsToField::class, $subFormField->getConfigValue('parent_type'));
        $this->assertEquals('platform.users', $subFormField->getConfigValue('resource'));
        $this->assertEquals($userField->getColumnName(), $subFormField->getColumnName());
    }

    function test__transform_to_subform_field()
    {
        $form = $this->getFormComponent($this->blueprints()->clients());

        $this->assertEquals('sub_form', $form->getField('user', 'type'));
    }
}