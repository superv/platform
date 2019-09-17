<?php

namespace Tests\Platform\Domains\Resource\Form\v2\Helpers;

use SuperV\Platform\Domains\Resource\Form\v2\FormFieldCollection;

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
