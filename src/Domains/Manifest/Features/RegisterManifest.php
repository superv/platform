<?php namespace SuperV\Platform\Domains\Manifest\Features;

use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\Manifest\DropletManifest;
use SuperV\Platform\Domains\Manifest\Manifest;
use SuperV\Platform\Domains\Manifest\ModelManifest;

class RegisterManifest extends Feature
{
    /**
     * @var Manifest
     */
    private $manifest;

    /**
     * @var Droplet
     */
    private $droplet;

    public function __construct(Manifest $manifest, Droplet $droplet)
    {
        $this->manifest = $manifest;
        $this->droplet = $droplet;
    }

    public function handle()
    {
        $manifest = is_object($this->manifest) ? $this->manifest : superv($this->manifest);

        if ($manifest instanceof ModelManifest) {

            return $this->dispatch(new RegisterModelManifest($manifest, $this->droplet));
        } elseif ($manifest instanceof DropletManifest) {

            return $this->dispatch(new RegisterDropletManifest($manifest, $this->droplet));
        }
    }
}