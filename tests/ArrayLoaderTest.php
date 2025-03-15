<?php

namespace Test\Loader;

use Loader\Config\ConfigLoader;
use PHPUnit\Framework\TestCase;

class ArrayLoaderTest extends TestCase
{
    public function testArrayLoader()
    {
        $file = __DIR__ . '/fixture/array_test.php';
        ConfigLoader::getInstance(ConfigLoader::ARRAY_LOADER, ['file' => $file])->load();
        $data = require $file;
        $key = array_key_first($data);
        $val = reset($data);
        $this->assertEquals(getenv($key), $val);
    }
}
