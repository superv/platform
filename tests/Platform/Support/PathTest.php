<?php

namespace Tests\Platform\Support;

use SuperV\Platform\Support\Path;
use Tests\Platform\TestCase;

class PathTest extends TestCase
{
    function test__parses_namespace_from_filename()
    {
        $namespace = 'SuperV\\Platform';
        $checkPath = '/Volumes/Users/dali/Projects/Lakcom/lakcom-project/_workbench/superv/platform/src/Resources/UserResource.php';
        $namespaceBasePath = '/Volumes/Users/dali/Projects/Lakcom/lakcom-project/_workbench/superv/platform/src';

        $expected = 'SuperV\\Platform\\Resources\\UserResource';
        $actualNamespace = Path::parseClass($namespace, $namespaceBasePath, $checkPath);
        $this->assertEquals($expected, $actualNamespace);

        $namespace = 'SuperV\\Modules\\Nucleo';
        $checkPath = '/Volumes/Users/dali/Projects/Lakcom/lakcom-project/_workbench/superv/modules/nucleo/src/Resources/ColumnResource.php';
        $namespaceBasePath = '/Volumes/Users/dali/Projects/Lakcom/lakcom-project/_workbench/superv/modules/nucleo/src';
        $expected = 'SuperV\\Modules\\Nucleo\\Resources\\ColumnResource';

        $actualNamespace = Path::parseClass($namespace, $namespaceBasePath, $checkPath);
        $this->assertEquals($expected, $actualNamespace);
    }
}