<?php

namespace SuperV\Platform\Domains\Resource\Form\v2;

use Illuminate\Http\Request;
use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormField;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\v2\Jobs\ComposeForm;
use SuperV\Platform\Domains\Resource\Form\v2\Jobs\RenderComponent;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Payload;

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

    public function handle(Request $request)
    {
        $this->fireEvent('handling');

        $this->setMethod(strtoupper($request->getMethod()));

        if ($this->isMethod('POST')) {
            $this->fireEvent('submitting');
        }

        $this->loadEntries($request->all());

        if ($this->isMethod('GET')) {
            $this->getFields()->keys()->map(function ($fieldIdentifier) {
                $fieldIdentifier = sv_identifier($fieldIdentifier);

                if ($entry = array_get($this->entries, $fieldIdentifier->getParent())) {
                    $fieldValue = $entry->getAttribute($fieldIdentifier->getTypeId());

                    $this->getField($fieldIdentifier)->setValue($fieldValue);

                    $this->setDataValue($fieldIdentifier, $fieldValue);
                }
            });
        } elseif ($this->isMethod('POST')) {
            foreach ($request->post() as $fieldIdentifier => $fieldValue) {
                $fieldIdentifier = sv_identifier(str_replace('_', '.', $fieldIdentifier));

                if ($entry = array_get($this->entries, $fieldIdentifier->getParent())) {
                    $entry->setAttribute($fieldIdentifier->getTypeId(), $fieldValue);

                    $this->setDataValue($fieldIdentifier, $fieldValue);
                }
            }

            foreach ($this->entries as $entry) {
                $entry->save();
            }
        }

//        ResolveRequest::resolve()->handle($this, $request);

        if ($request->isMethod('POST')) {
            $this->submitted = true;
            $this->fireEvent('submitted');
        }
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

    ////////////////////////////////////
    //
    ////////////////////////////////////

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

    public static function resolve(FormFieldCollection $fields, string $identifier)
    {
        $form = app(Contracts\FormInterface::class);

        $form->setIdentifier($identifier);
        $form->setFields($fields);

        return $form;
    }

    protected function loadEntries($requestArray)
    {
        foreach (array_pull($requestArray, 'entries', []) as $entry) {
            $parts = explode('.', $entry);

            if (count($parts) < 3 || ! is_numeric(end($parts))) {
                continue;
            }

            $entryId = array_pop($parts);
            $identifier = implode('.', $parts);

            if (! $entry = $this->entryRepository->getEntry($identifier, $entryId)) {
                continue;
            }

            $this->addEntry($identifier, $entryId);
            $this->entries[$identifier] = $entry;
        }
    }

    protected function setDataValue($key, $value)
    {
        $this->data[(string)$key] = $value;
    }

    public function setUrl($url): FormInterface
    {
        $this->url = $url;

        return $this;
    }

    public function getEntryIds(): array
    {
        return $this->entryIds ?? [];
    }

    public function getUrl(): string
    {
        if (! $this->url) {
            return sv_route(self::ROUTE, ['identifier' => $this->getIdentifier()]);
        }

        return $this->url;
    }

    public function addEntry($identifier, $id)
    {
        $this->entryIds[$identifier] = $id;
    }
}
