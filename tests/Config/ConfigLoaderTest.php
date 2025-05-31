<?php

use Loader\Config\ArrayLoader;
use Loader\Config\ConfigLoader;
use Loader\Config\EnvLoader;
use Loader\Config\JsonLoader;
use Loader\Config\XmlLoader;
use Loader\Config\YamlLoader;
use Loader\Exception\LoaderException;
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

    public function testGetInstanceInvalid()
    {
        $config = ['key' => 'value'];
        $this->expectException(LoaderException::class);
        $this->expectExceptionCode(LoaderException::LOADER_DRIVER_NOT_FOUND_ERROR);
        $loader = ConfigLoader::getInstance('dd', $config);
    }

    public function testGet()
    {
        $config = ['key' => 'value'];
        $loader = ConfigLoader::getInstance(ConfigLoader::VALUE_LOADER, $config, 'config');
        $loader = ConfigLoader::getConfig('config');
        $loader->setLoadHandler(function() {});
        $loader->load();
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

    public function testSetException()
    {
        $config = ['key' => 'value'];
        $loader = ConfigLoader::getInstance(ConfigLoader::ARRAY_LOADER, $config);
        $this->expectException(LoaderException::class);
        $this->expectExceptionCode(LoaderException::CONFIG_NOT_FOUND_ERROR);
        $loader->set('key1', 'new_value', true);
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
        $loader = ConfigLoader::getInstance(ConfigLoader::ARRAY_LOADER);
        $loader->override(['key1' => 'value1', 'key2' => 'value2']);
        $data = $loader->getAll();
        $this->assertArrayHasKey('key1', $data);
        $this->assertEquals('value1', $data['key1']);
        $this->assertArrayHasKey('key2', $data);
        $this->assertEquals('value2', $data['key2']);
    }

    public function testLoadJson()
    {
        $config = ConfigLoader::loadConfig(__DIR__ . '/../fixture/test.json');
        $this->assertEquals($config->getAll(), ['name' => 'test', 'value' => 1]);
    }

    public function testLoadYaml()
    {
        $config = ConfigLoader::loadConfig(__DIR__ . '/../fixture/test.yaml');
        $this->assertEquals($config->getAll(), ['name' => 'test', 'value' => 1]);
        $config = ConfigLoader::loadConfig(__DIR__ . '/../fixture/test.yml');
        $this->assertEquals($config->getAll(), ['name' => 'test', 'value' => 1]);
    }

    public function testXml()
    {
        $config = ConfigLoader::loadConfig(__DIR__ . '/../fixture/test.xml', 'config');
        $this->assertEquals($config->getAll(), ['name' => 'test', 'value' => 1]);
    }

    public function testLoadConfigWithInvalidFileType()
    {
        $this->expectException(LoaderException::class);
        $this->expectExceptionCode(LoaderException::FILE_TYPE_NOT_SUPPORTED_ERROR);
        ConfigLoader::loadConfig(__DIR__ . '/../fixture/test.txt');
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf(JsonLoader::class, ConfigLoader::getInstance(ConfigLoader::JSON_LOADER));
        $this->assertInstanceOf(XmlLoader::class, ConfigLoader::getInstance(ConfigLoader::XML_LOADER));
        $this->assertInstanceOf(YamlLoader::class, ConfigLoader::getInstance(ConfigLoader::YAML_LOADER));
    }
}
