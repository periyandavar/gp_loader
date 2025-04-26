<?php

use Loader\Config\ConfigLoader;
use Loader\Config\XmlLoader;
use Loader\Exception\LoaderException;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class XmlLoaderTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testInnerLoaderThrowsExceptionForUnreadableFile()
    {
        $loader = m::mock(XmlLoader::class);
        $loader->makePartial();
        $loader->shouldReceive('getFile')->andReturn('invalid.xml');
        $this->expectException(LoaderException::class);

        $loader->load();
    }

    public function testInnerLoaderThrowsExceptionForInvalidJson()
    {
        $this->expectException(LoaderException::class);

        ConfigLoader::loadConfig(__DIR__ . '/../fixture/invalid.xml');
    }
}
