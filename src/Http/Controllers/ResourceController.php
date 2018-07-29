<?php

namespace SuperV\Platform\Http\Controllers;

use SuperV\Platform\Domains\Nucleo\Nucleo;

/**
 * Class ResourceController
 *
 * @package Nucleo
 */
class ResourceController extends BaseController
{
    /** @var string|\SuperV\Platform\Domains\Entry\EntryModel */
    protected $model;

    protected function model()
    {
        if (! $this->model = Nucleo::modelOfTable($this->request->get('m'))) {
            throw new \Exception('Model needed here...');
        }

        return $this->model;
    }

    public function index()
    {
        return $this->model()::getTableBuilder()->response();
    }

    public function editor()
    {
        return $this->model()::getEditor()->response();
    }

    public function show($id)
    {
        $entry = $this->model()::query()->findOrFail($id);

        return ['data' => ['state' => $entry->compose()]];
    }

    public function store()
    {
        $entry = $this->model()::query()->create(request('state'));

        return ['data' => ['state' => $entry->compose()]];
    }

    public function update($id)
    {
        $entry = $this->model()::query()->findOrFail($id);
        $entry->update(request('state'));

        return ['data' => ['state' => $entry->compose()]];
    }

    public function delete($id)
    {
        $this->model()::query()->findOrFail($id)->delete();
    }
}