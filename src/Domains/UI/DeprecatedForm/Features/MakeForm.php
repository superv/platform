<?php

namespace SuperV\Platform\Domains\UI\DeprecatedForm\Features;

use Symfony\Component\Form\Form;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\UI\DeprecatedForm\FieldType;
use SuperV\Platform\Domains\UI\DeprecatedForm\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use SuperV\Platform\Domains\UI\DeprecatedForm\PropertyAccessor;
use SuperV\Platform\Domains\UI\DeprecatedForm\PropertyPathMapper;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MakeForm extends Feature
{
    /**
     * @var FormBuilder
     */
    private $builder;

    public function __construct(FormBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function handle(FormFactoryInterface $factory)
    {
        $form = $this->builder->getForm();
        $options = ['attr' => $this->builder->getFormAttributes(), 'action' => url()->current()];

        if ($this->builder->getEntry()->exists) {
            $form->setMode('edit');
        }

        /** @var FormBuilderInterface $symfonyFormBuilder */
        $symfonyFormBuilder = $factory->createBuilder(FormType::class, $this->builder->getEntry(), $options);

        $symfonyFormBuilder->setDataMapper(new PropertyPathMapper(new PropertyAccessor()));

        /** @var Form $symfonyForm */
        $symfonyForm = $symfonyFormBuilder->getForm();

        /** @var FieldType $field */
        foreach ($form->getFields() as $field) {
            if ($this->builder->hasSkip($field->getField())) {
                continue;
            }
            $symfonyForm->add($field->getField(), $field->getType(), $field->getOptions());
        }

        $symfonyForm->add('save', SubmitType::class,
            [
                'label' => 'Save',
                'attr'  => ['class' => 'btn-success'],
            ]
        );

        $form->setSymfonyForm($symfonyForm);
    }
}
