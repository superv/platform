<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Contracts\Hibernatable;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Support\Composition;
use SuperV\Platform\Support\Concerns\HibernatableConcern;

class AttachEntryAction extends Action implements Hibernatable
{
    use HibernatableConcern;

    protected $name = 'attach';

    protected $title = 'Attach New';

    /** @var \SuperV\Platform\Domains\Resource\Relation\Relation */
    protected $relation;

    public function makeComponent()
    {
        return parent::makeComponent()->setName('sv-attach-entry-action');
    }

    public function onComposed(Composition $composition)
    {
        $composition->replace('lookup-url', sv_url($this->getLookupUrl()));
        $composition->replace('attach-url', sv_url($this->getAttachUrl()));
    }

    public function getLookupUrl()
    {
        return sprintf(
            'sv/res/%s/%s/%s/lookup',
            $this->relation->getParentResourceHandle(),
            $this->relation->getParentResourceEntry()->getId(),
            $this->relation->getName()
        );
    }

    public function getAttachUrl()
    {
        return sprintf(
            'sv/res/%s/%s/%s/attach',
            $this->relation->getParentResourceHandle(),
            $this->relation->getParentResourceEntry()->getId(),
            $this->relation->getName()
        );
    }

    public function setRelation(Relation $relation): AttachEntryAction
    {
        $this->relation = $relation;

        return $this;
    }
}