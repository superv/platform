<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Relation;

use Illuminate\Database\Eloquent\Relations\BelongsTo as EloquentBelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany as EloquentHasMany;
use Illuminate\Database\Eloquent\Relations\HasOne as EloquentHasOne;
use Illuminate\Http\Request;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\Contracts\AltersDatabaseTable;
use SuperV\Platform\Domains\Resource\Field\Contracts\HandlesRpc;
use SuperV\Platform\Domains\Resource\Field\DoesNotInteractWithTable;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\FormData;
use SuperV\Platform\Domains\Resource\Jobs\CreatePivotTableV2;
use SuperV\Platform\Domains\Resource\Jobs\MakeLookupOptions;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Support\Composer\Payload;

class RelationField extends FieldType implements AltersDatabaseTable, DoesNotInteractWithTable, HandlesRpc
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $relatedResource;

    protected function boot()
    {
        $this->field->on('form.composing', $this->formComposer());
    }

    public function resolveDataFromRequest(FormData $data, Request $request, ?EntryContract $entry = null)
    {
        if (! $request->has($this->getName()) && ! $request->has($this->getColumnName())) {
            return null;
        }

        [$value, $requestValue] = $this->resolveValueFromRequest($request, $entry);

        $data->toSave($this->getColumnName(), $value);
    }

    public function getColumnName(): ?string
    {
        return $this->getConfig()->getLocalKey();
    }

    public function getRpcResult(array $params, array $request = [])
    {
        if (! $method = $params['method'] ?? null) {
            return null;
        }

        if (method_exists($this, $method = 'rpc'.studly_case($method))) {
            return call_user_func_array([$this, $method], [$params, $request]);
        }
    }

    public function rpcOptions(array $params, array $request = [])
    {
        return (new MakeLookupOptions($this->getRelatedResource(), $request['query'] ?? []))->make();
    }

    public function formComposer()
    {
        return function (Payload $payload, FormInterface $form, ?EntryContract $entry = null) {
            if ($entry) {
                if ($relatedEntry = $entry->{$this->getName()}()->newQuery()->first()) {
                    $payload->set('meta.link', $relatedEntry->router()->dashboardSPA());
                }
            }

            $options = $this->getConfigValue('meta.options');
            if (! is_null($options)) {
                $payload->set('meta.options', $options);
            } else {
                $route = $form->isPublic() ? 'sv::public_forms.fields' : 'sv::forms.fields';
                $url = sv_route($route, [
                    'form'  => $this->field->getForm()->getIdentifier(),
                    'field' => $this->getName(),
                    'rpc'   => 'options',
                ]);
                $payload->set('meta.options', $url);
            }

            $payload->set('placeholder', __('Select :Object', [
                'object' => $this->getRelatedResource()->getSingularLabel(),
            ]));
        };
    }

    protected function getRelatedResource()
    {
        if (! $this->relatedResource) {
            $this->relatedResource = ResourceFactory::make($this->getConfig()->getRelated());
        }

        return $this->relatedResource;
    }

    public function getType(): ?string
    {
        return 'belongs_to';
    }

    public function onMakingConfig(RelationFieldConfig $config)
    {
    }

    public function alterBlueprint(Blueprint $blueprint, array $config = [])
    {
        $config = new RelationFieldConfig($config);

        $blueprint->addPostBuildCallback(function (Blueprint $blueprint) use ($config) {
            if ($localKey = $config->getLocalKey()) {
                $blueprint->addColumn('integer', $localKey, ['nullable' => ! $config->isRequired()]);
            }
        });

        if ($pivotTable = $config->getPivotTable()) {
            (new CreatePivotTableV2)($config);
        }
    }

    public function newQuery(EntryContract $parent)
    {
        $config = $this->getConfig();
        $query = sv_resource($config->getRelated())->newQuery();

        if ($config->getRelationType()->isOneToOne()) {
            if ($config->getLocalKey()) {
                return new EloquentBelongsTo(
                    $query,
                    $parent,
                    $config->getLocalKey(),
                    'id',
                    $this->getName()
                );
            }

            if ($config->getForeignKey()) {
                return new EloquentHasOne(
                    $query,
                    $parent,
                    $config->getForeignKey(),
                    'id'
                );
            }
        }

        if ($config->getRelationType()->isOneToMany()) {
            if ($config->getForeignKey()) {
                return new EloquentHasMany(
                    $query,
                    $parent,
                    $config->getForeignKey() ?? $parent->getForeignKey(),
                    'id'
                );
            }
        }
    }

    public function getConfig(): RelationFieldConfig
    {
        return new RelationFieldConfig($this->field->getConfig());
    }
}
