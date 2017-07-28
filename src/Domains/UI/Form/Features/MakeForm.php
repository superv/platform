<?php namespace SuperV\Platform\Domains\UI\Form\Features;

use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\UI\Form\FieldType;
use SuperV\Platform\Domains\UI\Form\FormBuilder;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;

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

        $symfonyForm = $factory->create(FormType::class, $this->builder->getEntry(), []);

        /** @var FieldType $field */
        foreach ($form->getFields() as $field) {
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