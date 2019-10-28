<?php

namespace SuperV\Platform\Domains\Resource\Resource;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

/**
 * Trait LabelConcern
 *
 * @mixin \SuperV\Platform\Domains\Resource\Resource
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

    public function getSingularLabel($translated = true)
    {
        if (! $singularLabel = $this->config()->getSingularLabel()) {
            $singularLabel = str_singular($this->config()->getLabel());
        }

        if ($translated) {
            return __($singularLabel);
        }

        return $singularLabel;
    }

    public function getEntryLabelTemplate()
    {
        return $this->config()->getEntryLabel();
    }
}
