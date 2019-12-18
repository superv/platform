<?php

namespace Tests\Platform\Domains\Resource\Fixtures\FieldTypes\Genius;

use SuperV\Platform\Domains\Resource\Contracts\ComposerContext;
use SuperV\Platform\Domains\Resource\Field\Contracts\DecoratesFormComposer;
use SuperV\Platform\Domains\Resource\Field\FieldType;

class GeniusType extends FieldType implements DecoratesFormComposer
{
    protected $component = 'text';

    protected $handle = 'text';

    public function getComposerForContext(ComposerContext $context)
    {
        $contextKey = $context->getContextKey();
        $className = studly_case($contextKey.'_composer');

        $class = str_replace_last(class_basename(get_called_class()), $className, get_called_class());

        return new $class($this->field, $context);
    }

    public function getFormComposerDecoratorClass()
    {
        return FormComposerDecorator::class;
    }
}