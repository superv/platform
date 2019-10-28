<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\MorphMany as EloquentMorphMany;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Action\ModalAction;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Form\FormFactory;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Table\Table;

class MorphMany extends Relation implements ProvidesTable, ProvidesForm
{
    public function makeTable()
    {
        $relatedResource = $this->getRelatedResource();
        $modalAction = ModalAction::make($relatedResource->getChildIdentifier('actions', 'create'));

        return Table::resolve()
                    ->setResource($relatedResource)
                    ->setQuery($this)
                    ->setDataUrl(url()->current().'/data')
                    ->addContextAction(
                        $modalAction->setTitle('New '.str_singular(str_unslug($this->getName())))
                                    ->setModalUrl($this->route('create', $this->parentEntry))
                    );
    }

    public function makeForm($request = null): \SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface
    {
        $builder = FormFactory::builderFromResource($this->getRelatedResource());
        if ($request) {
            $builder->setRequest($request);
        }
        $builder->setEntry($childEntry = $this->newQuery()->make());

        return $builder->getForm();
    }

    public function getFormTitle(): string
    {
    }

    protected function newRelationQuery(?EntryContract $relatedEntryInstance = null): EloquentRelation
    {
        return new EloquentMorphMany(
            $relatedEntryInstance->newQuery(),
            $this->parentEntry,
            $this->relationConfig->getMorphName().'_type',
            $this->relationConfig->getMorphName().'_id',
            $this->relationConfig->getOwnerKey() ?? 'id',
        );
    }
}
