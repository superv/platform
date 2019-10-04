<?php

namespace SuperV\Platform\Domains\Resource;

use Platform;
use SuperV\Platform\Domains\Resource\Command\ResourceImportCommand;
use SuperV\Platform\Domains\Resource\Database\Entry\EntryRepository;
use SuperV\Platform\Domains\Resource\Database\Entry\EntryRepositoryInterface;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormBuilderInterface;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Form\FormBuilder;
use SuperV\Platform\Domains\Resource\Hook\HookManager;
use SuperV\Platform\Domains\Resource\Listeners\RegisterEntryEventListeners;
use SuperV\Platform\Domains\Resource\Relation\RelationCollection;
use SuperV\Platform\Domains\Resource\Table\Contracts\TableDataProviderInterface;
use SuperV\Platform\Domains\Resource\Table\Contracts\TableInterface;
use SuperV\Platform\Domains\Resource\Table\EloquentTableDataProvider;
use SuperV\Platform\Domains\Resource\Table\Table;
use SuperV\Platform\Providers\BaseServiceProvider;

class ResourceServiceProvider extends BaseServiceProvider
{
    protected $_bindings = [
        TableDataProviderInterface::class => EloquentTableDataProvider::class,
        EntryRepositoryInterface::class   => EntryRepository::class,
        TableInterface::class             => Table::class,

        FormBuilderInterface::class => FormBuilder::class,
        FormInterface::class        => Form::class,
    ];

    protected $_singletons = [
        'relations'        => RelationCollection::class,
        HookManager::class => HookManager::class,
    ];

    protected $commands = [ResourceImportCommand::class];

    public function register()
    {
        parent::register();

        $this->registerListeners((array)require($this->platform->realPath('config/resource/listeners.php')));
    }

    public function boot()
    {
        if (! Platform::isInstalled()) {
            return;
        }

        RegisterEntryEventListeners::dispatch();
    }
}
