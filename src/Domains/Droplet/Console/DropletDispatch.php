<?php namespace SuperV\Platform\Domains\Droplet\Console;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use SuperV\Platform\Domains\Feature\Command\FormulateFeature;
use Vizra\SupervModule\Drop\DropModel;
use Vizra\SupervModule\Server\ServerModel;
use Vizra\SupervModule\Server\ServerRepository;

class DropletDispatch extends Command
{
    use DispatchesJobs;
    
    protected $signature = 'droplet:dispatch {droplet} {feature} {--server= : }';
    
    protected $description = 'Runs droplet feature ';
    
    public function handle(ServerRepository $servers)
        {
            /** @var ServerModel $server */
            if (!$server = $servers->findBySlug($this->option('server'))) {
                throw new \Exception('Server not found');
            }
            
            /** @var DropModel $drop */
            if (!$drop = $server->getDropBySlug($this->argument('droplet'))) {
                throw new \Exception('Droplet not enabled on this server');
            }
            
            $this->dispatch(new FormulateFeature($drop,  $this->argument('feature')));
        
    }
}