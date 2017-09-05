<?php

namespace SuperV\Platform\Domains\Config\Jobs;

use Illuminate\Config\Repository;
use SplFileInfo;
use SuperV\Platform\Contracts\Filesystem;

class AddConfigNamespace
{
    private $namespace;

    private $directory;

    public function __construct($namespace, $directory)
    {
        $this->namespace = $namespace;
        $this->directory = $directory;
    }
    public function handle(Filesystem $files, Repository $config)
     {
         if (!is_dir($this->directory)) {
             return;
         }

         /* @var SplFileInfo $file */
         foreach ($files->allFiles($this->directory) as $file) {
             $key = trim(
                 str_replace(
                     $this->directory,
                     '',
                     $file->getPath()
                 ) . DIRECTORY_SEPARATOR . $file->getBaseName('.php'),
                 DIRECTORY_SEPARATOR
             );

             // Normalize key slashes.
             $key = str_replace('\\', '/', $key);

             $config->set($this->namespace . '::' . $key, $files->getRequire($file->getPathname()));
         }
     }

}