<?php

use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Domains\TaskManager\TaskModel;
use SuperV\Platform\Domains\TaskManager\TaskStatus;

class CreateTasksTable extends Migration
{
    public function up()
    {
        $this->create('sv_tasks',
            function (Blueprint $table, ResourceConfig $config) {
                $config->label('Tasks');
                $config->nav('acp.platform');
                // $config->resourceKey('');
                // $config->restorable();
                // $config->sortable();
                $config->model(TaskModel::class);

                $table->increments('id');
                $table->string('title')->entryLabel();
                $table->string('handler');
                $table->status(TaskStatus::class)->showOnIndex();
                $table->dictionary('payload');
                $table->text('info')->nullable()->showOnIndex();
                $table->createdBy()->updatedBy();
            });
    }

    public function down()
    {
        $this->dropIfExists('sv_tasks');
    }
}
