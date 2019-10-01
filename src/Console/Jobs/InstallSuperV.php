<?php

namespace SuperV\Platform\Console\Jobs;

use Artisan;
use Current;
use DB;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema as SchemaBuilder;
use Log;
use Schema;
use SuperV\Platform\Domains\Resource\Jobs\CreatePlatformResourceForms;
use SuperV\Platform\Domains\Resource\Listeners\RegisterEntryEventListeners;
use SuperV\Platform\Domains\Resource\ResourceServiceProvider;
use SuperV\Platform\Domains\Resource\Support\PlatformBlueprints;
use SuperV\Platform\Events\PlatformInstalledEvent;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Platform;
use SuperV\Platform\PlatformServiceProvider;
use SuperV\Platform\Support\Dispatchable;

class InstallSuperV
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Platform
     */
    protected $platform;

    protected $hostname = 'localhost';

    public function __construct(array $params = [])
    {
        if (isset($params['hostname'])) {
            $this->hostname = $params['hostname'];
        }
    }

    public function handle(Platform $platform)
    {
        $this->platform = $platform;

        $platformServiceProvider = new PlatformServiceProvider(app());

        DB::beginTransaction();

        try {
            $this->install($platformServiceProvider);
            $this->commit();
        } catch (Exception $e) {
            $this->rollback($e);
        }
    }

    /**
     * @param \Exception $e
     * @throws \SuperV\Platform\Exceptions\PlatformException
     */
    protected function rollback(Exception $e): void
    {
        DB::rollBack();

        EnvFile::load(base_path('.env'))->set('SV_INSTALLED', 'false')->write();

//        $this->setEnv('SV_INSTALLED=false');

        config(['superv.installed' => false]);

        Log::error($e->getMessage());

        PlatformException::throw($e);
    }

    protected function commit(): void
    {
        DB::commit();

        Artisan::call('vendor:publish', ['--tag' => 'superv.config']);

        PlatformInstalledEvent::dispatch();

        $this->platform->fire('install');
    }

    /**
     * @param \SuperV\Platform\PlatformServiceProvider $platformServiceProvider
     */
    protected function install(PlatformServiceProvider $platformServiceProvider): void
    {
        $this->prepareMigrationsTable();

        $platformServiceProvider->registerBase();
        $platformServiceProvider->bindUserModel();
        app()->register(ResourceServiceProvider::class);

        Current::setMigrationScope('platform');
        PlatformBlueprints::createTables();
        PlatformBlueprints::createResources();

        EnvFile::load(base_path('.env'))
               ->set('SV_INSTALLED', 'true')
               ->set('SV_HOSTNAME', $this->hostname)
               ->write();

        config(['superv.installed' => true]);


        Artisan::call('migrate', ['--namespace' => 'platform', '--force' => true]);

        CreatePlatformResourceForms::dispatch();

        app()->register(PlatformServiceProvider::class, true);

        RegisterEntryEventListeners::dispatch();
    }

    protected function prepareMigrationsTable(): void
    {
        if (! SchemaBuilder::hasTable('migrations')) {
            Artisan::call('migrate', ['--force' => true]);
        }

        if (! SchemaBuilder::hasColumn('migrations', 'namespace')) {
            Schema::table('migrations', function (Blueprint $table) {
                $table->string('namespace')->nullable();
            });
        }
    }
}
