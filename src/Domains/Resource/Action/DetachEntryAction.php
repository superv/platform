<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\Requirements\AcceptsEntry;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Support\Composer\Composition;

class DetachEntryAction extends Action implements AcceptsEntry
{
    protected $name = 'detach';

    protected $title = 'Detach';

    /** @var \SuperV\Platform\Domains\Resource\Relation\Relation */
    protected $relation;

    /** @var EntryContract */
    protected $entry;

    public function makeComponent()
    {
        return parent::makeComponent()
                     ->setName('sv-request-action');
    }

    public function onComposed(Composition $composition)
    {
        $composition->replace('url', sv_url($this->getRequestUrl()));
        $composition->replace('request', ['item' => $this->entry->id]);
        $composition->replace('on-complete', 'reload');
    }

    public function getRequestUrl()
    {
        return sprintf(
            'sv/res/%s/%s/%s/detach',
            $this->relation->getParentResourceHandle(),
            $this->relation->getParentEntry()->getId(),
            $this->relation->getName(),
            $this->entry->id
        );
    }

    public function setRelation(Relation $relation): self
    {
        $this->relation = $relation;

        return $this;
    }

    public function acceptEntry(EntryContract $entry)
    {
        $this->entry = $entry;
    }
}