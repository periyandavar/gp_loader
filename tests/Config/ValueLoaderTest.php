<?php

use Loader\Config\ConfigLoader;
use PHPUnit\Framework\TestCase;

class ValueLoaderTest extends TestCase
{
    public function testLoader()
    {
        $config = ['name' => 'value'];
        $loader = ConfigLoader::getInstance(ConfigLoader::VALUE_LOADER, $config)->load();

        $this->assertEquals($config, $loader->getAll());
    }

    public function testValidFileTypes()
    {
        $this->assertEmpty(ConfigLoader::getInstance(ConfigLoader::VALUE_LOADER)->getValidFileTypes());
    }
}
