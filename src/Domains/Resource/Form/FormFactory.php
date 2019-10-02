<?php

namespace SuperV\Platform\Domains\Resource\Form;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormBuilderInterface;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Exceptions\PlatformException;

class FormFactory
{
    public static function builderFromResource(Resource $resource): FormBuilderInterface
    {
        $formIdentifier = $resource->getIdentifier().'.forms:default';
        $builder = static::builderFromFormEntry($formIdentifier);

        return $builder;
    }

    public static function builderFromEntry(EntryContract $entry): FormBuilderInterface
    {
        $formIdentifier = $entry->getResourceIdentifier().'.forms:default';
        $builder = static::builderFromFormEntry($formIdentifier);
        $builder->setEntry($entry);

        return $builder;
    }

    public static function builderFromFormEntry($formEntry): FormBuilderInterface
    {
        if (is_string($identifier = $formEntry)) {
            if (! $formEntry = FormModel::withIdentifier($identifier)) {
                PlatformException::runtime('Form entry not found: '.$identifier);
            }
        }

        return FormBuilder::resolve()->setFormEntry($formEntry);
    }
}