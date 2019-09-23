<?php

namespace SuperV\Platform\Domains\Resource\Form\v2;

use Illuminate\Http\Request;
use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormField;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\v2\Jobs\ComposeForm;
use SuperV\Platform\Domains\Resource\Form\v2\Jobs\RenderComponent;
use SuperV\Platform\Domains\Resource\Form\v2\Jobs\ValidateForm;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Payload;
use SuperV\Platform\Support\Identifier;

class Form implements FormInterface
{
    const ROUTE = 'sv::forms.v2.show';

    protected $submitted = false;

    protected $valid = false;

    /** @var \SuperV\Platform\Domains\Resource\Form\v2\FormFieldCollection */
    protected $fields;

    /** @var string */
    protected $identifier;

    protected $url;

    protected $method = 'POST';

    /** @var Payload */
    protected $payload;

    /** @var \SuperV\Platform\Contracts\Dispatcher */
    protected $events;

    protected $data = [];

    protected $entryIds;

    protected $entries = [];

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\v2\EntryRepositoryInterface
     */
    protected $entryRepository;

    public function __construct(Dispatcher $events, EntryRepositoryInterface $entryRepository)
    {
        $this->events = $events;
        $this->entryRepository = $entryRepository;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data): FormInterface
    {
        $this->data = $data;

        return $this;
    }

    public function setFieldValue($key, $value): FormInterface
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function handle(Request $request): FormInterface
    {
        $this->fireEvent('handling');

        $this->setMethod(strtoupper($request->getMethod()));

        /**
         * Populate Form Data from Entries
         */
        if ($this->isMethod('GET')) {
            foreach ($request->get('entries', []) as $entryIdentifier) {
                $entryIdentifier = sv_identifier($entryIdentifier);
                if (! $entryIdentifier->type()->isEntry()) {
                    continue;
                }

                $entry = $this->entryRepository->getEntry($entryIdentifier->getParent(), $entryIdentifier->getTypeId());
                $this->entries[$entryIdentifier->getParent()] = $entry;
            }

            foreach ($this->entries as $resourceIdentifier => $entry) {
                $fields = $this->getFields()->getIdentifierMap()->get($resourceIdentifier);

                $this->data[$resourceIdentifier] = $entry->only($fields);
            }
        }

        /**
         * Populate Form Data from Request
         */
        if ($this->isMethod('POST')) {
            foreach ($request->get('entries', []) as $entryIdentifier) {
                $this->addEntry($entryIdentifier);
            }

//            sv_debug($request->post(), $this->getFields()->getIdentifierMap());
            foreach ($request->post() as $fieldIdentifier => $fieldValue) {
                $fieldIdentifier = sv_identifier(str_replace('_', '.', $fieldIdentifier));
                $this->setDataValue($fieldIdentifier, $fieldValue);
            }

        }

        return $this;
    }

    public function submit()
    {
        ValidateForm::resolve()->validate($this);

//        $this->fireEvent('submitting');

        foreach ($this->data as $resourceIdentifier => $entryData) {
            if ($entryId = array_get($this->entries, $resourceIdentifier)) {
                $this->entryRepository->update($resourceIdentifier.':'.$entryId, $entryData);
            } else {
                $this->entryRepository->create($resourceIdentifier, $entryData);
            }
        }

        $this->submitted = true;
//        $this->fireEvent('submitted');
    }

    public function compose(): Payload
    {
        $this->payload = ComposeForm::resolve()->handle($this);

        $this->fireEvent('composed', $this->payload);

        return $this->payload;
    }

    public function render(): ComponentContract
    {
        if (! $this->payload) {
            $this->compose();
        }

        return RenderComponent::resolve()->handle($this->payload);
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): FormInterface
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function isSubmitted(): bool
    {
        return $this->submitted;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getField(string $fieldName): ?FormField
    {
        return $this->getFields()->get($fieldName);
    }

    public function getFieldValue(string $fieldName)
    {
        return $this->getField($fieldName)->getValue();
    }

    public function getFields(): FormFieldCollection
    {
        return $this->fields;
    }

    public function setFields(FormFieldCollection $fields): FormInterface
    {
        $this->fields = $fields;

        return $this;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setMethod($method): FormInterface
    {
        $this->method = $method;

        return $this;
    }

    public function fireEvent(string $eventName, $payload = null)
    {
        $event = sprintf("%s.events:%s", $this->getIdentifier(), $eventName);

        $this->events->dispatch($event, array_filter(['form' => $this, 'payload' => $payload]));
    }

    public function isMethod($method): bool
    {
        return strtoupper($method) === $this->getMethod();
    }

    public function getDataValue($parent, $key)
    {
        return array_get(array_get($this->data, $parent, []), $key);
    }

    public function setValid(bool $valid): void
    {
        $this->valid = $valid;
    }

    public static function resolve(FormFieldCollection $fields, string $identifier)
    {
        $form = app(Contracts\FormInterface::class);

        $form->setIdentifier($identifier);
        $form->setFields($fields);

        return $form;
    }

    protected function setDataValue(Identifier $identifier, $value)
    {
        $parent = $this->data[$identifier->getParent()] ?? [];
        $parent[$identifier->getLastNode()] = $value;

        $this->data[$identifier->getParent()] = $parent;
    }

    public function setUrl($url): FormInterface
    {
        $this->url = $url;

        return $this;
    }

    public function getEntryIds(): array
    {
        $ids = [];
        foreach ($this->entries ?? [] as $resource => $entry) {
            $ids[] = $resource.':'.(is_object($entry) ? $entry->getId() : $entry);
        }

        return $ids;
    }

    public function getUrl(): string
    {
        if (! $this->url) {
            return sv_route(self::ROUTE, ['identifier' => $this->getIdentifier()]);
        }

        return $this->url;
    }

    public function addEntry($identifier, $id = null): FormInterface
    {
        if (is_null($id)) {
            $identifier = sv_identifier($identifier);
            $this->entries[$identifier->getParent()] = $identifier->id();
        } else {
            $this->entries[$identifier] = $id;
        }

        return $this;
    }
}
