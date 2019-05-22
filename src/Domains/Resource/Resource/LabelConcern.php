<?php

namespace SuperV\Platform\Domains\Resource\Resource;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

trait LabelConcern
{
    public function getLabel($translated = true)
    {
        $label = $this->addon.'::'.$this->getHandle().'.label';

        if (! $translated) {
            return $label;
        }

        return sv_trans($label);
    }

    public function getEntryLabel(EntryContract $entry)
    {
        return sv_parse($this->getConfigValue('entry_label'), $entry->toArray());
    }

    public function getSingularLabel()
    {
        return sv_trans($this->addon.'::'.$this->getHandle().'.singular_label');

        return $this->getConfigValue('singular_label', str_singular($this->getConfigValue('label')));
    }

    public function getEntryLabelTemplate()
    {
        return $this->getConfigValue('entry_label');
    }
}