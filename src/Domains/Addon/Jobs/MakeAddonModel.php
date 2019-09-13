<?php

namespace SuperV\Platform\Domains\Addon\Jobs;

use SuperV\Platform\Domains\Addon\AddonModel;
use SuperV\Platform\Support\Dispatchable;

class MakeAddonModel
{
    use Dispatchable;

    private $identifier;

    /**
     * @var null
     */
    private $path;

    protected $type;

    public function __construct($identifier, $type, $path = null)
    {
        $this->identifier = $identifier;

        $this->path = $path;

        $this->type = $type;
    }

    public function handle()
    {
//        if (! str_is('*.*.*', $this->identifier)) {
//            throw new \Exception('Slug should be snake case and formatted like: {vendor}.{type}.{name}');
//        }

        if (! preg_match('/^([a-zA-Z0-9_]+)\/([a-zA-Z0-9_]+)$/', $this->identifier)) {
            throw new \Exception('Identifier should be in this format: {vendor}/{package}');
        }

        list($vendor, $package) = array_map(
            function ($value) {
                return str_slug(strtolower($value), '_');
            },
            explode('/', $this->identifier)
        );

        // single point of truth
        $type = str_plural($this->type);
        $addonsDirectory = sv_config('addons.location');

        return new AddonModel([
            'vendor'        => $vendor,
            'package'       => $package,
            'type'          => str_singular($type),
            'name'          => $package,
            'path'          => $this->path ?: "{$addonsDirectory}/{$vendor}/{$type}/{$package}",
            'psr_namespace' => ucfirst(camel_case(($vendor == 'superv' ? 'super_v' : $vendor))).'\\'.ucfirst(camel_case($type)).'\\'.ucfirst(camel_case($package)),
            'enabled'       => false,
        ]);
    }
}
