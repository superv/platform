<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsEntry;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\UI\Components\UIComponent;
use SuperV\Platform\Support\Composer\Composition;

class DetachEntryAction extends Action implements AcceptsEntry
{
    protected $name = 'detach';

    protected $title = 'Detach';

    /** @var \SuperV\Platform\Domains\Resource\Relation\Relation */
    protected $relation;

    /** @var EntryContract */
    protected $entry;

    public function makeComponent(): UIComponent
    {
        return parent::makeComponent()
                     ->setName('sv-request-action');
    }

    public function onComposed(Composition $composition)
    {
        $composition->replace('url', sv_url($this->getRequestUrl()));
        $composition->replace('request', ['item' => '{entry.id}']);
        $composition->replace('on-complete', 'reload');
    }

    public function getRequestUrl()
    {
        return sprintf(
            'sv/res/%s/%s/%s/detach',
            $this->relation->getParentResourceHandle(),
            $this->relation->getParentEntry()->getId(),
            $this->relation->getName()
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