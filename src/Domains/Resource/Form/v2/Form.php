<?php

namespace SuperV\Platform\Domains\Resource\Form\v2;

use Illuminate\Http\Request;
use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormField;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\v2\Jobs\ComposeForm;
use SuperV\Platform\Domains\Resource\Form\v2\Jobs\RenderComponent;
use SuperV\Platform\Domains\Resource\Form\v2\Jobs\ResolveRequest;
use SuperV\Platform\Domains\Resource\Form\v2\Jobs\SubmitForm;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Payload;

class Form implements FormInterface
{
    const ROUTE = 'sv::forms.v2.show';

    protected $submitted = false;

    protected $valid = false;

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\v2\FormFieldCollection
     */
    protected $fields;

    /**
     * @var string
     */
    protected $identifier;

    protected $url;

    protected $method = 'POST';

    /** @var Payload */
    protected $payload;

    /**
     * @var \SuperV\Platform\Contracts\Dispatcher
     */
    protected $events;

    protected $data;

    protected $entryIds;

    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

    public function handle(Request $request)
    {
        $this->fireEvent('handling');

        ResolveRequest::resolve()->handle($this, $request);
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

    public function submit($data)
    {
        SubmitForm::resolve()->handle($this, $data);

        $this->submitted = true;
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
//        return $this->data[$fieldName];
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

    public static function resolve(FormFieldCollection $fields, string $identifier)
    {
        $form = app(Contracts\FormInterface::class);

        $form->setIdentifier($identifier);
        $form->setFields($fields);

        return $form;
    }

    protected function fireEvent(string $eventName, $payload = null)
    {
        $event = sprintf("%s.%s", $this->getIdentifier(), $eventName);

        $this->events->dispatch($event, ['form' => $this, 'payload' => $payload]);
    }

    public function setUrl($url): FormInterface
    {
        $this->url = $url;

        return $this;
    }

    public function getUrl(): string
    {
        if (! $this->url) {
            return sv_route(self::ROUTE, ['identifier' => $this->getIdentifier()]);
        }
        return $this->url;
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

    public function setMethod($method): FormInterface
    {
        $this->method = $method;

        return $this;
    }

    public function isMethod($method): bool
    {
        return strtoupper($method) === $this->getMethod();
    }

    public function getEntryIds(): array
    {
        return $this->entryIds ?? [];
    }

    public function addEntry($identifier, $id)
    {
        $this->entryIds[$identifier] = $id;
    }
}
