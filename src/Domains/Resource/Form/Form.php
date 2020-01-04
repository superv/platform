<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Closure;
use Event;
use Illuminate\Http\Request;
use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesUIComponent;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormFieldInterface;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\Jobs\ValidateForm;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\UI\Components\Component;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Payload;
use SuperV\Platform\Support\Concerns\FiresCallbacks;

class Form implements FormInterface, ProvidesUIComponent
{
    use FiresCallbacks;

    protected $method = 'POST';

    /** @var string */
    protected $identifier;

    /** @var Resource */
    protected $resource;

    /** @var EntryContract */
    protected $entry;

    /** @var \SuperV\Platform\Domains\Resource\Form\FormFields */
    protected $fields;

    /** @var string */
    protected $url;

    /** @var \Illuminate\Http\Request */
    protected $request;

    protected $actions = [];

    protected $postSaveCallbacks = [];

    protected $title;

    /**
     * @var \SuperV\Platform\Contracts\Dispatcher
     */
    protected $dispatcher;

    /** @var \SuperV\Platform\Domains\Resource\Form\FormData */
    protected $data;

    protected $public = false;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        $this->fields = new FormFields();
    }

    public function resolve(): FormInterface
    {
        Event::listen($this->getIdentifier().'.events:resolving', [$this->fields(), 'resolving']);
        Event::listen($this->getIdentifier().'.events:saving', [$this->fields(), 'saving']);
        Event::listen($this->getIdentifier().'.events:validating', [$this->fields(), 'validating']);
        Event::listen($this->getIdentifier().'.events:composed', [$this->fields(), 'composed']);

        if (! $this->data) {
            $this->data = new FormData($this->fields);
        }

        $this->fireEvent('resolving');

        if ($this->entry && $this->entry->exists()) {
            $this->resolveEntry();
//            $this->data->resolveEntry($this->entry);
        }

        if ($this->request) {
            $this->resolveRequest();
//            $this->data->resolveRequest($this->request, $this->entry);
        }

        $this->fireEvent('resolved');

        return $this;
    }

    public function resolveEntry(): FormInterface
    {
        $this->fields
            ->visible()
            ->map(function (FieldInterface $field) {
                $field->getValue()
                      ->setEntry($this->getEntry())
                      ->resolve()
                      ->mapTo($this->getData());
            });

        return $this;
    }

    public function resolveRequest(?Request $request = null): FormInterface
    {
        if ($request) {
            $this->setRequest($request);
        }

        $this->fields
            ->visible()
            ->map(function (FieldInterface $field) {
                $field->getValue()
                      ->setRequest($this->getRequest())
                      ->resolve()
                      ->mapTo($this->getData());
            });

        return $this;
    }

    public function validate()
    {
        $this->fireEvent('validating');

        ValidateForm::dispatch($this->fields, $this->data, $this->entry);
    }

    public function save(): FormResponse
    {
        $this->validate();

        $this->submit();

        return new FormResponse($this, $this->getEntry(), $this->resource);
    }

    public function submit()
    {
        $this->fireEvent('saving');

        $this->entry->fill($this->data->get());

        $this->entry->save();

        $this->data->callbacks()
                   ->filter()
                   ->map(function (Closure $callback) {
                       $callback($this->getEntry());
                   });

        $this->fields->saved($this);
    }

    public function compose(): Payload
    {
        $payload = FormComposer::make($this)->setRequest($this->request)->payload();

        $this->fireEvent('composed', ['payload' => $payload]);

        return $payload;
    }

    public function fireEvent($event, array $payload = [])
    {
        $eventName = sprintf("%s.events:%s", $this->getIdentifier(), $event);

        $payload = array_merge(['form' => $this, 'fields' => $this->fields()], $payload);

        $this->dispatcher->dispatch($eventName, $payload);
    }

    public function addField(FormFieldInterface $field)
    {
        return $this->fields()->addField($field);
    }

    public function fields(): FormFields
    {
        return $this->fields;
    }

    public function getFieldRpcUrl($fieldHandle, $rpcKey)
    {
        return sv_route('sv::forms.fields', [
            'form'  => $this->getIdentifier(),
            'field' => $fieldHandle,
            'rpc'   => 'options',
        ]);
    }

    public function setFields(FormFields $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    public function getField(string $name): ?FormFieldInterface
    {
        return $this->fields()->field($name);
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getEntry(): ?EntryContract
    {
        return $this->entry;
    }

    public function setEntry(?EntryContract $entry): FormInterface
    {
        $this->entry = $entry;

        return $this;
    }

    public function hasEntry(): bool
    {
        return (bool)$this->entry;
    }

    public function isUpdating()
    {
        return $this->hasEntry() && $this->getEntry()->exists();
    }

    public function isCreating()
    {
        return ! $this->isUpdating();
    }

    public function setData($data): FormInterface
    {
        $this->data = new FormData($this->fields, $data);

        return $this;
    }

    public function getData(): FormData
    {
        return $this->data;
    }

    public function setRequest(?Request $request): FormInterface
    {
        $this->request = $request;

        return $this;
    }

    public function setIdentifier(string $identifier): FormInterface
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function setActions(array $actions): void
    {
        $this->actions = $actions;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl(string $url): FormInterface
    {
        $this->url = $url;

        return $this;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function makeComponent(): ComponentContract
    {
        return Component::make('sv-form')
                        ->setProps(
                            $this->compose()->get()
                        );
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }
}
