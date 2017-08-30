<?php

namespace SuperV\Platform\Domains\UI\Form\Extension;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;

/**
 * Allows use of Symfony Validator in form.
 */
class FormValidatorExtension extends ValidatorExtension
{
    public function __construct()
    {
        $builder = Validation::createValidatorBuilder();

        $builder->setConstraintValidatorFactory(new ConstraintValidatorFactory());
        $builder->setMetadataFactory(new LazyLoadingMetadataFactory(new StaticMethodLoader()));

        parent::__construct($builder->getValidator());
    }
}
