<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Table\TableConfig;
use SuperV\Platform\Support\Composition;

class MultipleSelectAction extends Action
{
    protected $name = 'select_multiple';

    protected $title = 'Attach New';

    /** @var \SuperV\Platform\Domains\Resource\Relation\Relation */
    protected $relation;

    public function makeComponent()
    {
        return parent::makeComponent()->setName('sv-remote-action');
    }

    public function onComposed(Composition $composition)
    {
        $relatedResource = $this->relation->getRelatedResource();
        $config = new TableConfig();
        $config->setFields($relatedResource);
        $config->setQuery($relatedResource);

        $config->build();

        $composition->replace('url', sv_url($config->getUrl()));
    }

    public function getLookupTable()
    {
        $relatedResource = $this->relation->getRelatedResource();
        $config = new TableConfig();
        $config->setFields($relatedResource);
        $config->setQuery($relatedResource);

        return $config->build()->makeComponent()->compose();
    }

    public function setRelation(Relation $relation): self
    {
        $this->relation = $relation;

        return $this;
    }
}