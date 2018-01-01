<?php

usersnamespace SuperV\Platform\Domains\UI\DeprecatedForm\Jobs;

use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\UI\Form\FormBuilder;
use SuperV\Platform\Domains\UI\Button\Features\MakeButtons;

class MakeFormButtons extends Feature
{
    /**
     * @var FormBuilder
     */
    private $builder;

    public function __construct(FormBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function handle()
    {
        $form = $this->builder->getForm();

        $buttons = $this->dispatch(new MakeButtons($this->builder->getButtons(), ['entry' => $this->builder->getEntry()]));

        $form->setButtons($buttons);
    }
}
