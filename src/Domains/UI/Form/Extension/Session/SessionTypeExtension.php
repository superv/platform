<?php

namespace SuperV\Platform\Domains\UI\Form\Extension\Session;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;

class SessionTypeExtension extends AbstractTypeExtension
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->listener = new SessionListener();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->listener);
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return FormType::class;
    }
}
