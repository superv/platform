<?php

namespace SuperV\Platform\Domains\Table\Jobs;

use SuperV\Platform\Domains\Table\TableBuilder;

class SetTableModel
{
    /**
     * @var \SuperV\Platform\Domains\Table\TableBuilder
     */
    protected $builder;

    public function __construct(TableBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function handle()
    {
        $builder = $this->builder;

        $table = $builder->getTable();
        $model = $builder->getModel();

        if (is_object($model)) {
            $table->setModel($model);

            return;
        }

        if ($model === null) {
            $parts = explode('\\', str_replace('TableBuilder', 'Model', get_class($this->builder)));

            unset($parts[count($parts) - 2]);

            $model = implode('\\', $parts);
            if (!class_exists($model)){
                $model = str_replace(last($parts), "Model\\".last($parts), $model);
            }

            $this->builder->setModel($model);
        }

        if (! $model || ! class_exists($model)) {
            throw new \Exception('Model does not exist: '. $model);
        }

        $table->setModel(app($model));
    }
}