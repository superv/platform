<?php

namespace SuperV\Platform\Domains\Console\Jobs;

use SuperV\Platform\Domains\Database\Migration\MigrationCreator;
use SuperV\Platform\Domains\Database\Migration\Migrator;
use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Droplet\DropletFactory;
use Symfony\Component\Console\Input\InputInterface;

class ConfigureCreator
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

    /**
     * @var MigrationCreator
     */
    private $creator;

    /**
     * ConfigureCreator constructor.
     *
     * @param                  $droplet
     * @param InputInterface   $input
     * @param MigrationCreator $creator
     */
    public function __construct($droplet, InputInterface $input, MigrationCreator $creator)
    {
        $this->droplet = $droplet;
        $this->input = $input;
        $this->creator = $creator;
    }

    public function handle(DropletFactory $factory)
    {
        if (! $this->droplet) {
            return;
        }

        /** @var Droplet $droplet */
        if (! $droplet = $factory->fromSlug($this->droplet)) {
            throw new \InvalidArgumentException("Droplet {$this->droplet} not found");
        }
        $this->creator->setDroplet($droplet);

        $this->input->setArgument('name', $droplet->getSlug().'__'.$this->input->getArgument('name'));

        $this->input->setOption('path', $droplet->getPath('database/migrations'));

        if (! is_dir($directory = $droplet->getPath('database/migrations'))) {
            mkdir($directory);
        }
    }
}