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
    protected $addonType;

    protected $identifier;

    protected $addonPath;

    public function __construct(string $identifier, string $addonType)
    {
//        $this->vendor = $vendor;
//        $this->name = $name;
        $this->addonType = $addonType;
        $this->identifier = $identifier;
    }

    public function make()
    {
        $addonsDirectory = sv_config('addons.location');
        $vendor = $this->getVendor();
        $name = $this->getName();
        $typePlural = str_plural($this->addonType);

//        if (! $this->identifier) {
//            $this->identifier = sprintf("%s.%s.%s", $vendor, $typePlural, $name);
//        }

        if (! $this->addonPath) {
            $this->addonPath = sprintf("%s/%s/%s/%s", $addonsDirectory, $vendor, $typePlural, $name);
        }

        $psrNamespace = ucfirst(camel_case(($vendor == 'superv' ? 'super_v' : $vendor))).'\\'.ucfirst(camel_case($typePlural)).'\\'.ucfirst(camel_case($name));

        return new AddonModel([
            'identifier'    => $this->identifier,
            'name'          => $this->getName(),
            'vendor'        => $this->getVendor(),
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

    public function getVendor()
    {
        return explode('.', $this->identifier)[0];
    }

    public function getName()
    {
        return explode('.', $this->identifier)[1];
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
            $request->getIdentifier(),
            $request->getAddonType()
        );

        $self->setIdentifier($request->getIdentifier());
        $self->setAddonPath($request->getTargetPath());

        return $self->make();
    }
}
