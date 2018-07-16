<?php

namespace SuperV\Platform\Http\Controllers;

use Lakcom\Modules\Core\Domains\Shipment\Model\Shipment;
use Lakcom\Modules\Core\Domains\Warehouse\Location;

class EntryController extends BaseController
{
    /** @var string|\SuperV\Platform\Domains\Entry\EntryModel */
    protected $model;
    
    public function __construct() { 
        parent::__construct();

        if ($this->request->get('m') === 'locations') {
            $this->model = Location::class;
        } elseif ($this->request->get('m') === 'shipments') {
            $this->model = Shipment::class;
        } else {
            throw new \Exception('Model needed here...');
        }
    }

    public function index()
    {
        return $this->model::getTableBuilder()->response();
    }

    public function editor()
    {
        return $this->model::getEditor()->response();
    }

    public function show($id)
    {
        $entry = $this->model::query()->findOrFail($id);

        return ['data' => ['state' => $entry->compose()]];
    }

    public function store()
    {
        $entry = $this->model::query()->create(request('state'));

        return ['data' => ['state' => $entry->compose()]];
    }

    public function update($id)
    {
        $entry = $this->model::query()->findOrFail($id);
        $entry->update(request('state'));

        return ['data' => ['state' => $entry->compose()]];
    }

    public function delete($id)
    {
        $this->model::query()->findOrFail($id)->delete();
    }
}