<?php

namespace Test\Loader;

use Loader\Config\ConfigLoader;
use PHPUnit\Framework\TestCase;

class ArrayLoaderTest extends TestCase
{
    public function testArrayLoader()
    {
        $file = __DIR__ . '/../fixture/array_test.php';
        ConfigLoader::getInstance(ConfigLoader::ARRAY_LOADER, ['file' => $file])->load();
        $data = require $file;
        $key = array_key_first($data);
        $val = reset($data);
        $this->assertEquals(getenv($key), $val);
    }

    public function testHandler()
    {
        $handler_called = false;
        $callable = function ($data) use (&$handler_called) {
            $handler_called = true;
            return 'handler';
        };
        $file = __DIR__ . '/../fixture/array_test.php';
        $loader = ConfigLoader::getInstance(ConfigLoader::ARRAY_LOADER, ['file' => $file]);
        $loader->setLoadHandler($callable);
        $loader->load();
        $this->assertTrue($handler_called);
    }
}
