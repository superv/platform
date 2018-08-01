<?php

namespace SuperV\Platform\Domains\Nucleo\Resource;

use SuperV\Platform\Domains\Nucleo\Field;
use SuperV\Platform\Domains\Nucleo\Prototype;
use SuperV\Platform\Domains\Table\TableBuilder;

class Resource
{
    protected static $model;

    /** @var \SuperV\Platform\Domains\Nucleo\Prototype */
    protected $prototype;

    /** @var \SuperV\Platform\Domains\Nucleo\Resource\ResourceEntry */
    protected $entry;

    protected $fields = [];

    protected $httpRequest;

    protected $builded = false;

    public function build()
    {
        $modelInstance = $this->resolveModel();

        $this->prototype = Prototype::where('slug', $modelInstance->getTable())->first();

        $this->makeFields();

        $this->builded = true;

        return $this;
    }

    public function makeFields()
    {
        $allFields = $this->prototype->fields->keyBy('slug');

        $fields = [];
        foreach ($this->fields as $key => $value) {
            if (is_numeric($key)) {
                $slug = $value;
                $field = $this->getField($slug);
            } else {
                $slug = $key;
                $field = $value;
            }

            if ($static = array_get($allFields, $slug)) {
                $fields[$slug] = array_merge([
                    'label' => $static->label(),
                    'type'  => $this->getFieldType($static),
                ], $field);

                $fields[$slug]['config'] = array_get($field, 'config', ($static->config ?? []));
                $fields[$slug]['slug'] = array_get($field, 'slug', $static->slug);
            } else {
                $fields[$slug] = array_merge([
                    'slug' => $slug,
                ], $field);
            }
        }

        $this->fields = $fields;

        return $this;
    }

    public function create()
    {
        $this->entry = $this->resolveModel();
        $this->fillEntry();

        $this->entry->save();

        $this->processUploads();

        return $this;
    }

    public function load($entryId)
    {
        $this->entry = $this->getModel()::query()->findOrFail($entryId);

        return $this;
    }

    public function entry()
    {
        return $this->entry;
    }

    public function update()
    {
        $this->fillEntry();

        $this->entry->save();

        $this->processUploads();

        return $this;
    }

    public function delete()
    {
        $this->entry->delete();

        return $this;
    }

    public function fillEntry()
    {
        $request = $this->getHttpRequest();

        foreach ($this->fields as $key => $field) {
            if ($field['type'] !== 'file') {
                $value = $request->get($key);

                if ($field['type'] === 'boolean') {
                    $value = (bool)$value;
                }

                $this->entry->setAttribute($key, $value);
                if (! is_null($value)) {
                }
            }
        }
    }

    public function processUploads()
    {
        $request = $this->getHttpRequest();

        foreach ($this->fields as $key => $field) {
            if ($field['type'] === 'file' && $request->hasFile($key)) {
                $disk = array_pull($field, 'disk', 'local');
                $visibility = array_pull($field, 'visibility', 'private');
                $this->entry->mediaBag($key)->addFromUploadedFile($request->file($key), $disk, $visibility);
            }
        }
    }

    public function fields()
    {
        return $this->fields;
    }

    protected function getField($slug)
    {
        if (method_exists($this, $method = sprintf('get%sField', studly_case($slug)))) {
            return $this->{$method}($slug);
        }

        return [];
    }

    protected function getFieldType(Field $field)
    {
        $type = array_get($field->config, 'type', $field->type);

        $map = [
            'text'    => 'text',
            'string'  => 'text',
            'integer' => 'text',
            'decimal' => 'text',
        ];

        return array_get($map, $type, $type);
    }

    /** @return \SuperV\Platform\Domains\Nucleo\Resource\ResourceEditor */
    public function getEditor()
    {
        $class = str_replace_last('Resource', 'Editor', get_called_class());
        if (! class_exists($class)) {
            $class = ResourceEditor::class;
        }

        return app($class, ['resource' => $this]);
    }

    /** @return \SuperV\Platform\Domains\Nucleo\Resource\ResourceIndex */
    public function getIndex()
    {
        $class = str_replace_last('Resource', 'Index', get_called_class());
        if (! class_exists($class)) {
            $class = ResourceIndex::class;
        }

        return app($class, ['resource' => $this]);
    }

    /** @return \SuperV\Platform\Domains\Table\TableBuilder */
    public function getTableBuilder()
    {
        $class = str_replace_last('Resource', 'TableBuilder', get_called_class());
        if (! class_exists($class)) {
            $class = TableBuilder::class;
        }

        return app($class)->setModel(static::$model);
    }

    public function getModel()
    {
        return static::$model;
    }

    /** @return \Illuminate\Database\Eloquent\Model */
    public function resolveModel()
    {
        return app($this->getModel());
    }

    /**
     * @return mixed
     */
    public function getHttpRequest()
    {
        return $this->httpRequest ?? app('request');
    }
}