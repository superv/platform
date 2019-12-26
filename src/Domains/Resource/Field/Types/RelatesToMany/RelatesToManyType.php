<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\RelatesToMany;

use Illuminate\Database\Eloquent\Relations\HasMany as EloquentHasMany;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Action\EditEntryAction;
use SuperV\Platform\Domains\Resource\Action\ModalAction;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsParentEntry;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Field\Contracts\ProvidesRelationQuery;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Form\FormFactory;
use SuperV\Platform\Domains\Resource\Jobs\MakeLookupOptions;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class RelatesToManyType extends FieldType implements
    ProvidesRelationQuery,
    ProvidesTable,
    AcceptsParentEntry
{
    protected $handle = 'relates_to_many';

    /**
     * @var \SuperV\Platform\Domains\Resource\ResourceFactory
     */
    protected $factory;

    /**
     * @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract
     */
    protected $parentEntry;

    public function __construct(MakeLookupOptions $lookupOptions, ResourceFactory $factory)
    {
        $this->lookupOptions = $lookupOptions;
        $this->factory = $factory;
    }

    protected function boot()
    {
        $this->field->addFlag('view.hide');
    }

    public function getRelatedEntries(EntryContract $parent)
    {
        return $this->getRelationQuery($parent)->get();
    }

    public function getRelationQuery(EntryContract $parent)
    {
        $parentResource = ResourceFactory::make($parent);

        $config = $this->field->getConfig();

        return new EloquentHasMany(
            $this->getRelated()->newQuery(),
            $parent,
            $config['foreign_key'] ?? $parent->getForeignKey(),
            $parentResource->config()->getKeyName(),
        );
    }

    public function getRelated(): \SuperV\Platform\Domains\Resource\Resource
    {
        $config = $this->field->getConfig();

        return $this->factory->withIdentifier($config['related']);
    }

    public function route($name, EntryContract $entry, array $params = [])
    {
        $params = array_merge([
            'entry'    => $entry->getId(),
            'resource' => $entry->getResourceIdentifier(),
            'relation' => $this->getFieldHandle(),
        ], $params);

        return route('relation.'.$name, $params, false);
    }

    public function makeForm($request = null): \SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface
    {
        $builder = FormFactory::builderFromResource($this->getRelated());
        if ($request) {
            $builder->setRequest($request);
        }
        $builder->setEntry($childEntry = $this->getRelationQuery($this->parentEntry)->make());

        $form = $builder->getForm();

        $form->fields()->hide(sv_resource($this->parentEntry)->config()->getResourceKey());

        return $form;
    }

    public function makeTable()
    {
        $relatedResource = $this->getRelated();
        $editAction = EditEntryAction::make($relatedResource->getChildIdentifier('actions', 'edit'));

        $query = $this->getRelationQuery($this->parentEntry);

        return $relatedResource->resolveTable()
                               ->setQuery($query)
                               ->setDataUrl(sv_url()->path().'/data')
                               ->addRowAction($editAction)
                               ->addContextAction(
                                   ModalAction::make($relatedResource->getChildIdentifier('actions', 'create'))
                                              ->setTitle('New '.str_singular(str_unslug($this->getFieldHandle())))
                                              ->setModalUrl($this->parentEntry->router()->fieldAction($this->getFieldHandle(), 'create'))
                               );
    }

    public function acceptParentEntry(EntryContract $entry)
    {
        $this->parentEntry = $entry;
    }
}