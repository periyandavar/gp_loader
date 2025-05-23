<?php

use Loader\Config\ConfigLoader;
use Loader\Exception\LoaderException;
use PHPUnit\Framework\TestCase;

class ArrayLoaderTest extends TestCase
{
    private $arrayFilePath;

    protected function setUp(): void
    {
        // Create a temporary array file for testing
        $this->arrayFilePath = __DIR__ . '/../fixture/array_test1.php';
        file_put_contents($this->arrayFilePath, "<?php return ['key1' => 'value1', 'key2' => 'value2'];");
    }

    protected function tearDown(): void
    {
        // Remove the temporary array file after testing
        if (file_exists($this->arrayFilePath)) {
            unlink($this->arrayFilePath);
        }
    }

    public function testInnerLoader()
    {
        $config = ['file' => $this->arrayFilePath];
        $loader = ConfigLoader::getInstance(ConfigLoader::ARRAY_LOADER, $config);
        $data = $loader->innerLoader();

        $this->assertArrayHasKey('key1', $data);
        $this->assertEquals('value1', $data['key1']);
        $this->assertArrayHasKey('key2', $data);
        $this->assertEquals('value2', $data['key2']);
    }

    public function testGetFileWithInvalidType()
    {
        $this->expectException(LoaderException::class);
        $this->expectExceptionCode(LoaderException::FILE_TYPE_NOT_SUPPORTED_ERROR);
        $config = ['file' => $this->arrayFilePath];
        $loader = ConfigLoader::getInstance(ConfigLoader::ENV_LOADER, $config);
        $data = $loader->innerLoader();
    }

    public function testPhp()
    {
        $config = ConfigLoader::loadConfig($this->arrayFilePath);
        $this->assertSame(['key1' => 'value1', 'key2' => 'value2'], $config->getAll());
    }

    public function testInnerLoaderFileNotConfigured()
    {
        $this->expectException(LoaderException::class);
        $this->expectExceptionCode(LoaderException::FILE_NOT_FOUND_ERROR);

        $config = [];
        $loader = ConfigLoader::getInstance(ConfigLoader::ARRAY_LOADER, $config);
        $loader->innerLoader();
    }

    public function testInnerLoaderFileNotFound()
    {
        $this->expectException(LoaderException::class);
        $this->expectExceptionCode(LoaderException::FILE_NOT_FOUND_ERROR);

        $config = ['file' => 'non_existent_file.php'];
        $loader = ConfigLoader::getInstance(ConfigLoader::ARRAY_LOADER, $config);
        $loader->load();
    }
}
