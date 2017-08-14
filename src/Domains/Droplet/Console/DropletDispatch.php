<?php namespace SuperV\Platform\Domains\Droplet\Console;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use SuperV\Modules\Supreme\Domains\Server\Model\ServerModel;
use SuperV\Modules\Supreme\Domains\Server\Model\Servers;
use SuperV\Platform\Domains\Feature\Command\FormulateFeature;

class DropletDispatch extends Command
{
    use DispatchesJobs;
    
    protected $signature = 'droplet:dispatch {droplet} {feature} {--server= : }';
    
    protected $description = 'Runs droplet feature ';
    
    public function handle(Servers $servers)
        {
            /** @var ServerModel $server */
            if (!$server = $servers->find($this->option('server'))) {
                throw new \Exception('Server not found');
            }
            
            /** @var DropModel $drop */
            if (!$drop = $server->getDropBySlug($this->argument('droplet'))) {
                throw new \Exception('Droplet not enabled on this server');
            }
            
            $this->dispatch(new FormulateFeature($drop,  $this->argument('feature')));
        
    }
}