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

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $addonPath;

    public function __construct(string $identifier, string $addonType)
    {
        $this->addonType = $addonType;
        $this->identifier = $identifier;
    }

    public function make()
    {
        $addonsDirectory = sv_config('addons.location');
        $namespace = $this->parseNamespace();
        $handle = $this->parseHandle();
        $typePlural = str_plural($this->addonType);

        if (! $this->addonPath) {
            $this->addonPath = sprintf("%s/%s/%s/%s", $addonsDirectory, $namespace, $typePlural, $handle);
        }

        $psrNamespace = ucfirst(camel_case(($namespace == 'superv' ? 'super_v' : $namespace))).'\\'.ucfirst(camel_case($typePlural)).'\\'.ucfirst(camel_case($handle));

        return new AddonModel([
            'namespace'     => $namespace,
            'handle'        => $handle,
            'identifier'    => $handle,
            //            'identifier'    => $namespace.'.'.$handle,
            'type'          => str_singular($this->addonType),
            'path'          => $this->addonPath,
            'psr_namespace' => $psrNamespace,
            'enabled'       => false,
        ]);
    }

    public function setIdentifier($identifier): void
    {
        $this->identifier = $identifier;
    }

    public function parseNamespace()
    {
        return explode('.', $this->identifier)[0];
    }

    public function parseHandle()
    {
        $handle = explode('.', $this->identifier)[1];

        // Strip trailing {-type} from addon name
        if (ends_with($handle, '-'.$this->addonType)) {
            return str_replace_last('-'.$this->addonType, '', $handle);
        }

        return $handle;
    }

    public function getAddonPath()
    {
        return $this->addonPath;
    }

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
