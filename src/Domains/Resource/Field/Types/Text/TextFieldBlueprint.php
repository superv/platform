<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Text;

use SuperV\Platform\Domains\Resource\Builder\FieldBlueprint;

class TextFieldBlueprint extends FieldBlueprint
{
    public function useAsEntryLabel(): TextFieldBlueprint
    {
        $this->entryLabel = true;

        return $this;
    }

    public function maxLength(int $length)
    {
        return $this->addRule('max:'.$length);
    }
}