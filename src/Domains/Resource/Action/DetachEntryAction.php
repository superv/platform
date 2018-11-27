<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsEntry;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Composition;

class DetachEntryAction extends Action
{
    protected $name = 'detach';

    protected $title = 'Detach';

    /** @var \SuperV\Platform\Domains\Resource\Relation\Relation */
    protected $relation;

    /** @var EntryContract */
    protected $entry;

    public function makeComponent(): ComponentContract
    {
        return parent::makeComponent()
                     ->setName('sv-request-action');
    }

    public function onComposed(Composition $composition)
    {
        $composition->replace('url', str_replace('entry.id', '{entry.id}', $this->getRequestUrl()));
        $composition->replace('on-complete', 'reload');
    }

    public function getRequestUrl()
    {
        return $this->relation->route('detach', $this->relation->getParentEntry(), ['related' => 'entry.id']);

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