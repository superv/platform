<?php

namespace SuperV\Platform\Domains\Resource\Form\v2\Jobs;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\v2\EntryRepositoryInterface;

class ResolveRequest
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Form\v2\EntryRepositoryInterface
     */
    protected $repository;

    protected $entries = [];

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormInterface
     */
    protected $form;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(EntryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function handle(FormInterface $form, Request $request)
    {
        $this->form = $form;
        $this->request = $request;
        $form->setMethod(strtoupper($request->getMethod()));

        $requestArray = $request->all();

        foreach (array_pull($requestArray, 'entries', []) as $entry) {
            $parts = explode('.', $entry);

            if (count($parts) < 3 || ! is_numeric(end($parts))) {
                continue;
            }

            $entryId = array_pop($parts);
            $identifier = implode('.', $parts);
            $form->addEntry($identifier, $entryId);

            if (! $entry = $this->repository->getEntry($identifier, $entryId)) {
                continue;
            }

            $this->entries[$identifier] = $entry;
        }

        if ($form->isMethod('GET')) {
            $form->getFields()->keys()->map(function ($fieldIdentifier) {
                list($entryIdentifier, $fieldKey) = explode('.fields:', $fieldIdentifier);

                if ($entry = array_get($this->entries, $entryIdentifier)) {
                    $this->form->getField($fieldIdentifier)->setValue($entry->getAttribute($fieldKey));
                }
            });
        } elseif ($form->isMethod('POST')) {
            foreach ($this->request->post() as $fieldIdentifier => $value) {
                list($entryIdentifier, $fieldKey) = explode('.fields:', str_replace('_', '.', $fieldIdentifier));
                if ($entry = array_get($this->entries, $entryIdentifier)) {
                    $entry->setAttribute($fieldKey, $value);
                }
            }

            foreach ($this->entries as $entry) {
                $entry->save();
            }
        }
    }

    /**
     * @return static
     */
    public static function resolve()
    {
        return app(static::class);
    }
}
