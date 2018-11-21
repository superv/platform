<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Resource\Contracts\Requirements\AcceptsResourceEntry;
use SuperV\Platform\Domains\Resource\Model\Contracts\ResourceEntry;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Support\Composition;
use SuperV\Platform\Support\Concerns\HibernatableConcern;

class DetachEntryAction extends Action implements AcceptsResourceEntry
{
    use HibernatableConcern;

    protected $name = 'detach';

    protected $title = 'Detach';

    /** @var \SuperV\Platform\Domains\Resource\Relation\Relation */
    protected $relation;

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
            $this->relation->getParentResourceEntry()->getId(),
            $this->relation->getName(),
            $this->entry->id
        );
    }

    public function setRelation(Relation $relation): self
    {
        $this->relation = $relation;

        return $this;
    }

    /** @var \SuperV\Platform\Domains\Database\Model\Entry */
    protected $entry;

    public function acceptResourceEntry(ResourceEntry $entry)
    {
        $this->entry = $entry;
    }
}