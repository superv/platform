<?php

namespace SuperV\Platform\Domains\Resource\Resource;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

/**
 * Trait LabelConcern
 *
 * @package SuperV\Platform\Domains\Resource\Resource
 * @method \SuperV\Platform\Domains\Resource\ResourceConfig config()
 */
trait LabelConcern
{
    public function getLabel()
    {
        return $this->config()->getLabel();
    }

    public function getEntryLabel(EntryContract $entry)
    {
        return sv_parse($this->config()->getEntryLabel(), $entry->toArray());
    }

    public function getSingularLabel()
    {
        $key = $this->getNamespace().'::resources.'.$this->getIdentifier().'.singular';
        if ($value = trans($key)) {
            return __($value);
        }
        if (! $singularLabel = $this->config()->getSingularLabel()) {
            $singularLabel = str_singular($this->config()->getLabel());
        }

        return __($singularLabel);
    }

    public function getEntryLabelTemplate()
    {
        return $this->config()->getEntryLabel();
    }
}
