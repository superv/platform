<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Text;

use SuperV\Platform\Domains\Resource\Blueprint\FieldBlueprint;

class TextFieldBlueprint extends FieldBlueprint
{
    public function useAsEntryLabel(): TextFieldBlueprint
    {
        $this->entryLabel = true;

        return $this;
    }
}