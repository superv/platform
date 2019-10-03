<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Closure;
use Illuminate\Http\Request;
use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesUIComponent;
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

    /** @var \SuperV\Platform\Domains\Resource\Form\FormFieldCollection */
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

    public function __construct(string $identifier = null, Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->identifier = $identifier;

        $this->fields = new FormFieldCollection();

        $this->data = new FormData($this->fields);
    }

    public function save(): FormResponse
    {
//        $this->fireBeforeSavingCallbacks();

//        $this->resolveFieldValuesFromRequest();

        $this->validate();

        $this->submit();

        return new FormResponse($this, $this->getEntry(), $this->resource);
    }

    public function submit()
    {
        $this->entry->fill($this->data->get());
        $this->entry->save();

        $this->data->callbacks()
                   ->filter()
                   ->map(function (Closure $callback) {
                       $callback();
                   });
    }

    public function addField(FormFieldInterface $field)
    {
        return $this->fields()->addField($field);
    }

    public function fields(): FormFieldCollection
    {
        return $this->fields;
    }

    public function setFields(FormFieldCollection $fields)
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

    public function setEntry(EntryContract $entry): FormInterface
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

    public function validate()
    {
        ValidateForm::dispatch($this->fields, $this->data, $this->entry);
    }

    public function setData(FormData $data): FormInterface
    {
        $this->data = $data;

        return $this;
    }

    public function getData(): FormData
    {
        return $this->data;
    }

    public function resolveRequest(Request $request): FormInterface
    {
        $this->request = $request;

        return $this;
    }

    public function compose(): Payload
    {
        return FormComposer::make($this)->setRequest($this->request)->payload();
    }

    public function makeComponent(): ComponentContract
    {
        return Component::make('sv-form')
                        ->setProps(
                            $this->compose()->get()
                        );
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

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setIdentifier(string $identifier): Form
    {
        $this->identifier = $identifier;

        return $this;
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

    /**
     * @param string $identifier
     * @return static
     */
    public static function resolve(string $identifier = null)
    {
        return app()->make(static::class, ['identifier' => $identifier]);
    }
}
