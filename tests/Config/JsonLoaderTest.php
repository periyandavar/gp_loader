<?php

use Loader\Config\ConfigLoader;
use Loader\Config\JsonLoader;
use Loader\Exception\LoaderException;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class JsonLoaderTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testInnerLoaderThrowsExceptionForUnreadableFile()
    {
        $loader = m::mock(JsonLoader::class);
        $loader->makePartial();
        $loader->shouldReceive('getFile')->andReturn('invalid.json');
        $this->expectException(LoaderException::class);

        $loader->load();
    }

    public function testInnerLoaderThrowsExceptionForInvalidJson()
    {
        $this->expectException(LoaderException::class);

        ConfigLoader::loadConfig(__DIR__ . '/../fixture/invalid.json');
    }
}
