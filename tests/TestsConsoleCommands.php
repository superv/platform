<?php

namespace Tests;

use Illuminate\Console\Command;
use Illuminate\Foundation\Application;

trait TestsConsoleCommands
{
    protected function runCommand(Command $command, $input = [])
    {
        return $command->run(
            new \Symfony\Component\Console\Input\ArrayInput($input),
            new \Symfony\Component\Console\Output\NullOutput
        );
    }

    protected function makeInputMatcher($expected)
    {
        return new InputMatcher($expected);
    }

    protected function makeApplicationStub(array $data = [])
    {
        return new ApplicationStub($data);
    }
}

class ApplicationStub extends Application
{
    public function __construct(array $data = [])
    {
        foreach ($data as $abstract => $instance) {
            $this->instance($abstract, $instance);
        }
    }

    public function environment()
    {
        return 'development';
    }
}

class InputMatcher extends \Mockery\Matcher\MatcherAbstract
{
    /**
     * @param  \Symfony\Component\Console\Input\ArrayInput $actual
     *
     * @return bool
     */
    public function match(&$actual)
    {
        return (string)$actual == $this->_expected;
    }

    public function __toString()
    {
        return '';
    }
}
