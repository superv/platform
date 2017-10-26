<?php

namespace SuperV\Platform\Domains\Database\Migration;

use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Support\Parser;
use Symfony\Component\Console\Input\InputInterface;

class MigrationCreator extends \Illuminate\Database\Migrations\MigrationCreator
{
    /** @var InputInterface */
    protected $input = null;

    /** @var  Droplet */
    protected $droplet;

    /**
     * Get the migration stub file.
     *
     * @param  string $table
     * @param  bool   $create
     *
     * @return string
     */
//    protected function getStub($table, $create)
//    {
//        if (is_null($table)) {
//            $file = $this->files->get($this->getStubPath().'/blank.stub');
//        }  else {
//            $stub = $create ? 'create.stub' : 'update.stub';
//
//                        $file = $this->getStubPath()."/{$stub}";
//        }
//
//        return  $this->files->get($file);
//
//    }

    /**
     * Populate the place-holders in the migration stub.
     *
     * @param  string $name
     * @param  string $stub
     * @param  string $table
     *
     * @return string
     */
    protected function populateStub($name, $stub, $table)
    {
        $class = $this->getClassName($name);

        return app(Parser::class)->parse($stub, compact('class', 'table'));
    }

    /**
     * Get the class name of a migration name.
     *
     * @param  string $name
     *
     * @return string
     */
    protected function getClassName($name)
    {
        return studly_case(str_replace('.', '_', $name));
    }

    /**
     * Get the path to the stubs.
     *
     * @return string
     */
    public function stubPath()
    {
        return superv('platform')->getResourcePath('stubs/database/migrations');
    }

    /**
     * Set the command input.
     *
     * @param  InputInterface $input
     *
     * @return $this
     */
    public function setInput(InputInterface $input)
    {
        $this->input = $input;

        return $this;
    }

    /**
     * @return Droplet
     */
    public function getDroplet(): Droplet
    {
        return $this->droplet;
    }

    /**
     * @param Droplet $droplet
     *
     * @return MigrationCreator
     */
    public function setDroplet(Droplet $droplet): MigrationCreator
    {
        $this->droplet = $droplet;

        return $this;
    }
}
