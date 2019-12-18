<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Text;

use SuperV\Platform\Domains\Resource\Builder\FieldBlueprint;

class Blueprint extends FieldBlueprint
{
    public function useAsEntryLabel(): Blueprint
    {
        $this->entryLabel = true;

        return $this;
    }

    public function maxLength(int $length)
    {
        return $this->addRule('max:'.$length);
    }
}