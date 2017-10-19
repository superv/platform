<?php

namespace SuperV\Platform\Domains\Console\Jobs;

use SuperV\Platform\Domains\Database\Migration\Migrator;
use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Droplet\DropletFactory;
use SuperV\Platform\Domains\Droplet\Model\DropletCollection;
use SuperV\Platform\Domains\Droplet\Model\Droplets;
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

    public function handle(DropletFactory $factory)
    {
        if (!$this->droplet) {
            return;
        }

        /** @var Droplet $droplet */
        if (! $droplet = $factory->fromSlug($this->droplet)) {
            throw new \InvalidArgumentException("Droplet {$this->droplet} not found");
        }
        $this->migrator->setDroplet($droplet);

        $this->input->setOption('path', $droplet->getPath('database/migrations'));
    }
}