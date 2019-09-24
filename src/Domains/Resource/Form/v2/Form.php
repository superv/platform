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
use SuperV\Platform\Exceptions\PlatformException;
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

    protected $entries = [];

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\v2\EntryRepositoryInterface
     */
    protected $entryRepository;

    protected $action;

    /**
     * @var array
     */
    protected $requestArray;

    protected $requestEntries = [];

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

    public function setRequest(Request $request): FormInterface
    {
        $this->requestArray = $request->all();
        $this->action = array_pull($this->requestArray, '__form_action');

        $this->requestEntries = array_pull($this->requestArray, 'entries', []);
        $this->setMethod(strtoupper($request->getMethod()));

        return $this;
    }

    public function handle(Request $request = null): FormInterface
    {
        $this->fireEvent('handling');

        if ($request) {
            $this->setRequest($request);
        }

        /**
         * Populate Form Data from Entries
         */
        if ($this->isMethod('GET')) {
            foreach ($this->requestEntries as $entryIdentifier) {
                $entryIdentifier = sv_identifier($entryIdentifier);
                if (! $entryIdentifier->type()->isEntry()) {
                    continue;
                }

                $resourceIdentifier = $entryIdentifier->getParent();

                $entry = $this->entryRepository->getEntry($entryIdentifier);

                $fields = $this->getFields()->getIdentifierMap()->get($resourceIdentifier);

                $this->data[$resourceIdentifier] = $entry->only($fields);
            }
        }

        /**
         * Populate Form Data from Request
         */
        if ($this->isMethod('POST')) {
//            foreach ($this->requestEntries  as $entryIdentifier) {
//                $this->addEntry($entryIdentifier);
//            }

//            sv_debug($_POST, $this->requestArray);
//            sv_debug($request->post(), $this->getFields()->getIdentifierMap());
            foreach ($this->requestArray as $fieldIdentifier => $fieldValue) {
                $fieldIdentifier = sv_identifier(str_replace('__', '.', $fieldIdentifier));
                $this->setDataValue($fieldIdentifier, $fieldValue);
            }
//            sv_debug($this->requestArray, $this->data);

        }

        return $this;
    }

    public function getEntry(string $identifier)
    {
        return $this->entryRepository->getEntry($identifier);
    }

    public function submit()
    {
        ValidateForm::resolve()->validate($this);

        $entries = [];
        foreach ($this->requestEntries as $identifier) {
            $identifier = sv_identifier($identifier);
            $entries[$identifier->getParent()] = $identifier->id();
        }

        foreach ($this->data as $resourceIdentifier => $entryData) {
            if ($entryId = array_get($entries, $resourceIdentifier)) {
                $this->entryRepository->update($resourceIdentifier.':'.$entryId, $entryData);
            } else {
                $this->entryRepository->create($resourceIdentifier, $entryData);
            }
        }

        $this->submitted = true;
    }

    public function getResponse()
    {
        return FormResponse::resolve()->build($this);
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

    public function identifier(): Identifier
    {
        return sv_identifier($this->getIdentifier());
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

    public function setValid(bool $valid): void
    {
        $this->valid = $valid;
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

    /**
     * @return mixed
     */
    public function getFormAction()
    {
        return $this->action;
    }

    public function getRequestEntries(): array
    {
        return $this->requestEntries;
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
        PlatformException::fail('deprecated');

        if (is_null($id)) {
            $identifier = sv_identifier($identifier);
            $this->entries[$identifier->getParent()] = $identifier->id();
        } else {
            $this->entries[$identifier] = $id;
        }

        return $this;
    }
}
