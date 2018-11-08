<?php

namespace SuperV\Platform\Domains\Resource\Field;

interface FieldObserver
{
    public function fieldValueUpdated(FieldValue $field);
}