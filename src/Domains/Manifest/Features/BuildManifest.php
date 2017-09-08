<?php

namespace SuperV\Platform\Domains\Manifest\Features;

use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;
use SuperV\Platform\Domains\Manifest\Jobs\BuildManifestPages;
use SuperV\Platform\Domains\Manifest\Jobs\MakeManifestPages;
use SuperV\Platform\Domains\Manifest\Jobs\SetManifestData;
use SuperV\Platform\Domains\Manifest\Jobs\SetManifestModel;
use SuperV\Platform\Domains\Manifest\ManifestBuilder;
use SuperV\Platform\Domains\UI\Page\Features\MakePages;

class BuildManifest
{
    use ServesFeaturesTrait;
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
        $builder = $this->builder;

        $this->dispatch(new SetManifestData($builder));

        $this->dispatch(new SetManifestModel($builder));

        $this->dispatch(new BuildManifestPages($builder));

        $this->serve(new MakePages($builder->getManifest()->getPages()));

    }
}