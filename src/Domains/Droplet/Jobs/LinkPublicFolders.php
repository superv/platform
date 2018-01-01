<?php

namespace SuperV\Platform\Domains\Droplet\Jobs;

use SuperV\Platform\Domains\Droplet\Model\DropletModel;

class LinkPublicFolders
{
    /**
     * @var DropletModel
     */
    private $model;

    public function __construct(DropletModel $model)
    {
        $this->model = $model;
    }

    public function handle()
    {
        $model = $this->model;
        if (in_array($model->getType(), ['theme'])) {
            $publicPath = public_path(str_plural($model->getType()));
            if (! file_exists($publicPath)) {
                mkdir($publicPath);
            }
            $where = $publicPath."/".$model->getName();
            if (! file_exists($where)) {
                $what = base_path($model->getPath('public'));
                symlink($what, $where);
            }
        }
    }
}