<?php

use Loader\Config\ArrayLoader;
use Loader\Config\ConfigLoader;
use Loader\Config\EnvLoader;
use PHPUnit\Framework\TestCase;

class ConfigLoaderTest extends TestCase
{
    public function testGetInstanceEnvLoader()
    {
        $config = ['key' => 'value'];
        $loader = ConfigLoader::getInstance(ConfigLoader::ENV_LOADER, $config);
        $this->assertInstanceOf(EnvLoader::class, $loader);
    }

    public function testGetInstanceArrayLoader()
    {
        $config = ['key' => 'value'];
        $loader = ConfigLoader::getInstance(ConfigLoader::ARRAY_LOADER, $config);
        $this->assertInstanceOf(ArrayLoader::class, $loader);
    }

    public function testGet()
    {
        $config = ['key' => 'value'];
        $loader = ConfigLoader::getInstance(ConfigLoader::ARRAY_LOADER, $config);
        $loader->override($config);
        $this->assertEquals('value', $loader->get('key'));
    }

    public function testSet()
    {
        $config = ['key' => 'value'];
        $loader = ConfigLoader::getInstance(ConfigLoader::ARRAY_LOADER, $config);
        $loader->set('key', 'new_value');
        $this->assertEquals('new_value', $loader->get('key'));
    }

    public function testMerge()
    {
        $config = ['key1' => 'value1'];
        $loader = ConfigLoader::getInstance(ConfigLoader::ARRAY_LOADER, $config);
        $loader->merge(['key2' => 'value2']);
        $this->assertEquals('value2', $loader->get('key2'));
    }

    public function testOverride()
    {
        $config = ['key1' => 'value1'];
        $loader = ConfigLoader::getInstance(ConfigLoader::ARRAY_LOADER, $config);
        $loader->override(['key2' => 'value2']);
        $this->assertEquals('value2', $loader->get('key2'));
        $this->assertNull($loader->get('key1'));
    }

    
    public function testGetAll()
    {
        $config = ['file' => ''];
        $loader = ConfigLoader::getInstance(ConfigLoader::ARRAY_LOADER);
        $loader->override(['key1' => 'value1', 'key2' => 'value2']);
        $data = $loader->getAll();
        $this->assertArrayHasKey('key1', $data);
        $this->assertEquals('value1', $data['key1']);
        $this->assertArrayHasKey('key2', $data);
        $this->assertEquals('value2', $data['key2']);
    }
}