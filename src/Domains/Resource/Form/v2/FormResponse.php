<?php

namespace SuperV\Platform\Domains\Resource\Form\v2;

use SuperV\Platform\Contracts\Arrayable;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class FormResponse implements Arrayable
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormInterface
     */
    protected $form;

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\v2\EntryRepositoryInterface
     */
    protected $entryRepository;

    protected $response;

    public function __construct(EntryRepositoryInterface $entryRepository)
    {
        $this->entryRepository = $entryRepository;
    }

    public function get()
    {
        return $this->response;
    }

    public function build(FormInterface $form)
    {
        $this->form = $form;
        $action = $form->getFormAction();

        $resource = ResourceFactory::make(array_keys($form->getData())[0]);

        $this->response = [
            'message'     => 'Form submmited successfulldy',
            'action'      => $action,
            'redirect_to' => $route ?? $resource->router()->dashboardSPA(),
        ];

        return $this;

        if (! $this->resource) {
            $this->resource = ResourceFactory::make($this->entry->getResourceIdentifier());
        }

        if ($action === 'view') {
            $route = $this->resource->spaRoute('entry.dashboard', $this->entry, ['section' => 'view']);
        } elseif ($action === 'create_another') {
            $route = $this->resource->spaRoute('dashboard', null, ['section' => 'create']);
        } elseif ($action === 'edit_next') {
            $next = $this->resource->newQuery()->where('id', '>', $this->entry->getId())->first();
            if ($next) {
                $route = $this->resource->route('forms.edit', $next).'?action=edit_next';
            }
        }

        $entryLabel = $this->resource->getEntryLabel($this->entry);

        return response()->json([
            'data' => [
                'message'     => __($this->form->isUpdating() ? ':Resource :Entry was updated' : ':Resource :Entry was created', ['Entry'    => $entryLabel,
                                                                                                                                  'Resource' => $this->resource->getSingularLabel()]),
                'action'      => $action,
                'redirect_to' => $route ?? $this->resource->spaRoute('dashboard'),
            ],
        ]);
    }

    public function toArray()
    {
        return $this->get();
    }

    /** * @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}
