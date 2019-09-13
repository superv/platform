<?php

namespace SuperV\Platform\Domains\Addon\Jobs;

use SuperV\Platform\Domains\Addon\AddonModel;
use SuperV\Platform\Domains\Addon\Features\MakeAddonRequest;
use SuperV\Platform\Support\Dispatchable;

class MakeAddonModel
{
    use Dispatchable;

    /**
     * @var string
     */
    protected $vendor;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $addonType;

    protected $identifier;

    protected $addonPath;

    public function __construct(string $vendor, string $name, string $addonType)
    {
        $this->vendor = $vendor;
        $this->name = $name;
        $this->addonType = $addonType;
    }

    public function make()
    {
        $addonsDirectory = sv_config('addons.location');

        if (! $this->identifier) {
            $this->identifier = sprintf("%s.%s.%s", $this->vendor, str_plural($this->addonType), $this->name);
        }

        if (! $this->addonPath) {
            $this->addonPath = sprintf("%s/%s/%s/%s", $addonsDirectory, $this->vendor, str_plural($this->addonType), $this->name);
        }

        $psrNamespace = ucfirst(camel_case(($this->vendor == 'superv' ? 'super_v' : $this->vendor))).'\\'.ucfirst(camel_case($this->addonType)).'\\'.ucfirst(camel_case($this->name));

        return new AddonModel([
            'vendor'        => $this->vendor,
            'name'          => $this->name,
            'identifier'    => $this->identifier,
            'type'          => str_singular($this->addonType),
            'path'          => $this->addonPath,
            'psr_namespace' => $psrNamespace,
            'enabled'       => false,
        ]);
    }

    /**
     * @param mixed $identifier
     */
    public function setIdentifier($identifier): void
    {
        $this->identifier = $identifier;
    }

    /**
     * @return mixed
     */
    public function getAddonPath()
    {
        return $this->addonPath;
    }

    /**
     * @param mixed $addonPath
     */
    public function setAddonPath($addonPath): void
    {
        $this->addonPath = $addonPath;
    }

    public static function makeFromRequest(MakeAddonRequest $request)
    {
        $self = new MakeAddonModel(
            $request->getVendor(),
            $request->getPackage(),
            $request->getAddonType()
        );

        $self->setIdentifier($request->getIdentifier());
        $self->setAddonPath($request->getTargetPath());

        return $self->make();
    }
}
