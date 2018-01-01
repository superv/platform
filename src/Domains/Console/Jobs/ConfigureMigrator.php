<?php

namespace SuperV\Platform\Domains\Console\Jobs;

use SuperV\Platform\Domains\Database\Migration\Migrator;
use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Droplet\DropletFactory;
use SuperV\Platform\Domains\Droplet\DropletCollection;
use SuperV\Platform\Domains\Droplet\Droplets;
use Symfony\Component\Console\Input\InputInterface;

class ConfigureMigrator
{
    /**
     * @var Migrator
     */
    private $migrator;

    /**
     * @var Droplet|string
     */
    private $droplet;

    /**
     * @var InputInterface
     */
    private $input;

    public function __construct(Migrator $migrator, $droplet, InputInterface $input)
    {
        $this->migrator = $migrator;
        $this->droplet = $droplet;
        $this->input = $input;
    }

    public function handle(Droplets $droplets)
    {
        if (!$this->droplet) {
            return;
        }

        /** @var Droplet $droplet */
        if (! $droplet = $droplets->withSlug($this->droplet)) {
            throw new \InvalidArgumentException("Droplet {$this->droplet} not found");
        }
        $this->migrator->setDroplet($droplet);

        $this->input->setOption('path', $droplet->getPath('database/migrations'));
    }
}