<?php

namespace Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SuperV\Platform\Support\RelativePath;

class RelativePathTest extends TestCase
{
    /** @test */
    function return_original_path_if_not_a_real_path()
    {
        $base = '/some/path';
        $path = 'resources/views';

        $relative = (new RelativePath($base))->get($path);

        $this->assertEquals('resources/views', $relative);
    }

    /** @test */
    function fail_if_given_base_path_is_not_a_real_path()
    {
        $base = 'some/relative/path';
        $path = 'resources/views';

        $this->expectException(InvalidArgumentException::class);

        $relative = (new RelativePath($base))->get($path);

        $this->assertEquals('resources/views', $relative);
    }

    /** @test */
    function get_relative_path()
    {
        $base = '/addons/superv/platform';
        $path = '/addons/superv/platform/resources/views';

        $relative = (new RelativePath($base))->get($path);

        $this->assertEquals('resources/views', $relative);
    }

    /** @test */
    function get_relative_path_when_same()
    {
        $base = '/addons/superv/platform';
        $path = '/addons/superv/platform';

        $relative = (new RelativePath($base))->get($path);

        $this->assertEquals('./', $relative);
    }

    /** @test */
    function get_relative_path_when_outside_base_by_one_level()
    {
        $base = '/addons/superv/platform';
        $path = '/addons/superv/modules/guard';

        $relative = (new RelativePath($base))->get($path);

        $this->assertEquals('../modules/guard', $relative);
    }

    /** @test */
    function get_relative_path_when_outside_base_by_two_levels()
    {
        $base = '/addons/superv/platform/src';
        $path = '/addons/superv/modules/guard';

        $relative = (new RelativePath($base))->get($path);

        $this->assertEquals('../../modules/guard', $relative);
    }
}

//
//"/Volumes/Users/dali/Projects/Lakcom/lakcom-project/_workbench/superv/modules/guard"
//"/Volumes/Users/dali/Projects/Lakcom/lakcom-project/_workbench/superv/platform"