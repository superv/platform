<?php

namespace SuperV\Platform\Domains\Manifest\Jobs;

use SuperV\Platform\Domains\Manifest\ManifestBuilder;

class SetManifestModel
{
    /**
     * @var ManifestBuilder
     */
    private $builder;

    public function __construct(ManifestBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function handle()
    {
        $manifest = $this->builder->getManifest();
        $model = $manifest->getModel();

        if ($model === null) {
            $parts = explode('\\', str_replace('Manifest', 'Model', $this->builder->getDataModel()));

            $model = implode('\\', $parts);

            if (class_exists($model)) {
                $manifest->setModel($model);
            } else {
                $model = str_replace(last($parts), 'Model\\'.last($parts), $model);
                if (class_exists($model)) {
                    $manifest->setModel($model);
                } else {
                    throw new \Exception('Manifest model not found '.$model);
                }
            }
        }
    }
}