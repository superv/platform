<?php

namespace SuperV\Platform\Domains\Resource\Resource;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

trait LabelConcern
{
    public function getLabel()
    {
        return $this->getConfigValue('label');
    }

    public function getEntryLabel(EntryContract $entry)
    {
        return sv_parse($this->getConfigValue('entry_label'), $entry->toArray());
    }

    public function getSingularLabel()
    {
        return $this->getConfigValue('singular_label', str_singular($this->getConfigValue('label')));
    }

    public function getEntryLabelTemplate()
    {
        return $this->getConfigValue('entry_label');
    }
}