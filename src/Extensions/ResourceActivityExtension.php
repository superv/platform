<?php

namespace SuperV\Platform\Extensions;

use Carbon\Carbon;
use Faker\Factory;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Action\DeleteEntryAction;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsResource;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ObservesEntrySaved;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ObservesEntrySaving;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\Table\ResourceTable;
use SuperV\Platform\Domains\UI\Nucleo\Chart;
use SuperV\Platform\Domains\UI\Nucleo\PieChart;
use SuperV\Platform\Domains\UI\Page\ResourcePage;

class ResourceActivityExtension implements ExtendsResource
{
    /** @var Resource */
    protected $resource;

    public function extend(Resource $resource)
    {
        $resource->onIndexPage(function (ResourcePage $page) {
            $page->setActions([]);
        });

        $resource->onIndexConfig(function (ResourceTable $table) {
            $table->addRowAction(DeleteEntryAction::class);
        });
        $fields = $resource->indexFields();
        $fields->show('entry');
        $fields->show('user')->copyToFilters();
        $fields->show('resource')->copyToFilters();

        $resource->searchable(['email']);
    }

    public function extends(): string
    {
        return 'sv_activities';
    }



}