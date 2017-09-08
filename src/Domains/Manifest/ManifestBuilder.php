<?php

namespace SuperV\Platform\Domains\Manifest;

use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;
use SuperV\Platform\Domains\Manifest\Features\BuildManifest;

class ManifestBuilder
{
    use ServesFeaturesTrait;

    /** @var  Droplet */
    private $droplet;

    /**
     * @var Manifest
     */
    private $manifest;

    private $dataModel;

    private $pages;

    public function __construct(Manifest $manifest)
    {
        $this->manifest = $manifest;
    }

    public function reset()
    {
        return app(ManifestBuilder::class);
    }

    public function build()
    {
        $this->dispatch(new BuildManifest($this));

        return $this;
    }

    /**
     * @return Manifest
     */
    public function getManifest(): Manifest
    {
        return $this->manifest;
    }

    /**
     * @return mixed
     */
    public function getDataModel()
    {
        return $this->dataModel;
    }

    /**
     * @param mixed $dataModel
     *
     * @return ManifestBuilder
     */
    public function setDataModel($dataModel)
    {
        $this->dataModel = $dataModel;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @param mixed $pages
     *
     * @return ManifestBuilder
     */
    public function setPages($pages)
    {
        $this->pages = $pages;

        return $this;
    }

    /**
     * @param Droplet $droplet
     *
     * @return ManifestBuilder
     */
    public function setDroplet(Droplet $droplet): ManifestBuilder
    {
        $this->droplet = $droplet;

        return $this;
    }

    /**
     * @return Droplet
     */
    public function getDroplet(): Droplet
    {
        return $this->droplet;
    }
}