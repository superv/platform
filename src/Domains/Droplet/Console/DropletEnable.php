<?php namespace SuperV\Platform\Domains\Droplet\Console;

use Illuminate\Console\Command;
use Vizra\SupervModule\Drop\DropModel;
use Vizra\SupervModule\Server\ServerModel;
use Vizra\SupervModule\Server\ServerRepository;

class DropletEnable extends Command
{
    protected $signature = 'droplet:enable {droplet} {server}';
    
    protected $description = 'Enables droplet on server';
    
    public function handle(ServerRepository $servers)
        {
            /** @var ServerModel $server */
            if (!$server = $servers->findBySlug($this->argument('server'))) {
                throw new \Exception('Server not found');
            }
            
            /** @var DropModel $drop */
            $drop = $server->getDropBySlug($this->argument('droplet'));
        
    }
}