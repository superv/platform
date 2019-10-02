<?php

namespace Tests\Platform\Support;

use SuperV\Platform\Support\JsonFile;
use Tests\Platform\TestCase;

class JsonFileTest extends TestCase
{
    function test__successfully_loads_json_file_into_array()
    {
        $json = JsonFile::fromPath($this->basePath('__fixtures__/sample.json'));

        $expected = json_decode(file_get_contents($this->basePath('__fixtures__/sample.json')), true);
        $this->assertNotEmpty($expected);
        $this->assertEquals($expected, $json->get());
    }

    function test__successfully_loads_json_string_into_array()
    {
        $json = JsonFile::fromString('{"type": "project", "version": "^1.2"}');

        $expected = json_decode('{"type": "project", "version": "^1.2"}', true);
        $this->assertNotEmpty($expected);
        $this->assertEquals($expected, $json->get());
    }

    function test__get_with_key_dot_notation()
    {
        $jsonFile = $this->getJsonFile();

        $this->assertEquals('project', $jsonFile->get('type'));
        $this->assertEquals('7.3', $jsonFile->get('require.php'));
        $this->assertNull($jsonFile->get('not-really'));
    }

    function test__merge_to_parent()
    {
        $jsonFile = $this->getJsonFile();

        $jsonFile->merge('require', ['superv/platform' => '1.0.0']);
        $this->assertEquals([
            'php'             => '7.3',
            'superv/platform' => '1.0.0',
        ], $jsonFile->get('require'));

        $jsonFile->merge(['name' => 'superV Project']);
        $this->assertEquals('superV Project', $jsonFile->get('name'));
    }

    function test__remove_data()
    {
        $jsonFile = $this->getJsonFile();
        $jsonFile->remove('autoload.psr-4');

        $this->assertEquals(['classmap' => [
            "database/seeds",
        ]], $jsonFile->get('autoload'));
    }

    function test__write()
    {
        $tmpJsonPath = $this->basePath('__fixtures__/tmp.json');
        file_put_contents($tmpJsonPath, json_encode(['foo' => 'bar']));

        $jsonFile = JsonFile::fromPath($tmpJsonPath);
        $jsonFile->write();

        $this->assertEquals(['foo' => 'bar'], json_decode(file_get_contents($tmpJsonPath), true));
    }

    function test__writes_to_another_file()
    {
        $jsonFile = $this->getJsonFile();
        $jsonFile->write($this->basePath('__fixtures__/tmp.json'));

        $this->assertEquals(
            trim(file_get_contents($this->basePath('__fixtures__/sample.json'))),
            file_get_contents($this->basePath('__fixtures__/tmp.json'))
        );

        unlink($this->basePath('__fixtures__/tmp.json'));
    }

    protected function getJsonFile(): JsonFile
    {
        $jsonString = <<<EOT
{
    "type": "project",
    "require": {
        "php": "7.3"
    },
    "autoload": {
        "classmap": [
            "database/seeds"
        ],
        "psr-4": {
            "App\\\": "app/"
        }
    },
    "extra": {
        "merge-plugin": {
            "include": [
                "addons/*/*/*/composer.json"
            ]
        }
    },
    "prefer-stable": true
}
EOT;

//        $this->assertEquals($jsonString, file_get_contents($this->basePath('__fixtures__/sample.json')));
        $json = JsonFile::fromString($jsonString);

        return $json;
    }
}


