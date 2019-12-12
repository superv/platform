<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types\HasMany;

use Illuminate\Database\Eloquent\Relations\HasMany as EloquentHasMany;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Action\EditEntryAction;
use SuperV\Platform\Domains\Resource\Action\ModalAction;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Form\FormFactory;
use SuperV\Platform\Domains\Resource\Relation\Relation;

class HasMany extends Relation implements ProvidesTable, ProvidesForm
{
    public function makeTable()
    {
        $relatedResource = $this->getRelatedResource();
        $editAction = EditEntryAction::make($relatedResource->getChildIdentifier('actions', 'edit'));

        return $relatedResource->resolveTable()
                               ->setQuery($this->newQuery())
                               ->setDataUrl(sv_url()->path().'/data')
                               ->addRowAction($editAction)
                               ->addContextAction(
                                   ModalAction::make($relatedResource->getChildIdentifier('actions', 'create'))
                                              ->setTitle('New '.str_singular(str_unslug($this->getName())))
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

        $form = $builder->getForm();

        $form->fields()->hide(sv_resource($this->parentEntry)->config()->getResourceKey());

        return $form;
    }

    public function getFormTitle(): string
    {
    }

    protected function newRelationQuery(?EntryContract $relatedEntryInstance = null): EloquentRelation
    {
        if (! $localKey = $this->relationConfig->getLocalKey()) {
            if ($this->parentEntry) {
                $entry = $this->parentEntry;
                $localKey = $entry->getKeyName();
            }
        }

        return new EloquentHasMany(
            $relatedEntryInstance->newQuery(),
            $this->parentEntry,
            $this->relationConfig->getForeignKey() ?? $this->parentEntry->getForeignKey(),
            $localKey ?? 'id'
        );
    }
}
