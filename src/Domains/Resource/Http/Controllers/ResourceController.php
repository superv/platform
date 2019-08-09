<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Field\Contracts\HandlesRpc;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Http\Controllers\BaseApiController;

class ResourceController extends BaseApiController
{
    use ResolvesResource;

    public function delete()
    {
        $resource = $this->resolveResource();

        $this->entry->delete();

        return ['data' => [
            'message' => __(':Resource :Entry was deleted', ['Entry'    => $resource->getEntryLabel($this->entry),
                                                             'Resource' => $resource->getSingularLabel()])]];
    }

    public function restore()
    {
        $resource = $this->resolveResource(false);

        /** @var    \SuperV\Platform\Domains\Resource\Model\ResourceEntry $entry */
        $entry = $resource->newQuery()->withTrashed()->find(request()->route()->parameter('id'));

        $entry->restore();
    }

    public function fields()
    {
        $this->resolveResource();

        $fieldName = $this->route->parameter('field');
        if (! $field = $this->resource->fields()->get($fieldName)) {
            PlatformException::fail("Field not found : [{$fieldName}]");
        }

        if (! $rpcMethod = $this->route->parameter('rpc')) {
            $composed = (new FieldComposer($field))->forForm();

            return ['data' => sv_compose($composed)];
        }

        if ($field->getFieldType() instanceof HandlesRpc) {
            return $field->getFieldType()->getRpcResult(['method' => $rpcMethod], $this->request->toArray());
        }

        return abort(404);
    }
}
