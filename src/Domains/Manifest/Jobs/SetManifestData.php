<?php

namespace SuperV\Platform\Domains\Manifest\Jobs;

use Illuminate\Bus\Dispatcher;
use SuperV\Platform\Domains\Manifest\ManifestBuilder;

class SetManifestData
{
    /**
     * @var ManifestBuilder
     */
    private $builder;

    public function __construct(ManifestBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function handle(Dispatcher $bus)
    {
        $manifest = $this->builder->getManifest();
        $dataModel = $this->builder->getDataModel();

        $data = $bus->dispatchNow((new $dataModel));

        $this->builder->setPages(array_get($data, 'pages'));

        $manifest->setPort(array_get($data, 'port'));

        $manifest->setDroplet($this->builder->getDroplet());

        if($model = array_get($data, 'model')) {
            $manifest->setModel($model);
        }
     }
}