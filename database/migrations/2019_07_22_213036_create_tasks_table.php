<?php

use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Domains\TaskManager\TaskModel;

class CreateTasksTable extends Migration
{
    public function up()
    {
        $this->create('sv_tasks',
            function (Blueprint $table, ResourceConfig $config) {
                // $config->label('{resource.label}');
                $config->nav('acp.platform');
                // $config->resourceKey('');
                // $config->restorable();
                // $config->sortable();
                $config->model(TaskModel::class);

                $table->increments('id');
                $table->string('title')->entryLabel();
                $table->string('handler');
                $table->select('status', ['pending', 'processing', 'done', 'error']);
                $table->dictionary('payload');
                $table->createdBy()->updatedBy();
            });
    }

    public function down()
    {
        $this->dropIfExists('sv_tasks');
    }
}
