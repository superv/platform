<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Table\TableConfig;
use SuperV\Platform\Support\Composition;
use SuperV\Platform\Support\Concerns\HibernatableConcern;

class MultipleSelectAction extends Action
{
    use HibernatableConcern;

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
//        $url = sprintf('sv/act/%s', uuid());
//
//        cache()->forever($url, serialize($this));

//        $composition->replace('url', sv_url($url));

        $relatedResource = $this->relation->getRelatedResource();
        $config = new TableConfig();
        $config->setFieldsProvider($relatedResource);
        $config->setQueryProvider($relatedResource);

        $config->build();

        $composition->replace('url', sv_url($config->getUrl()));
    }

    public function getLookupTable()
    {
        $relatedResource = $this->relation->getRelatedResource();
        $config = new TableConfig();
        $config->setFieldsProvider($relatedResource);
        $config->setQueryProvider($relatedResource);

        return $config->build()->makeComponent()->compose();
    }

    public function setRelation(Relation $relation): AttachEntryAction
    {
        $this->relation = $relation;

        return $this;
    }
}