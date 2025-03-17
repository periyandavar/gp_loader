<?php

use Loader\Config\ConfigLoader;
use PHPUnit\Framework\TestCase;

class EnvLoaderTest extends TestCase
{
    private $envFilePath;

    protected function setUp(): void
    {
        // Create a temporary .env file for testing
        $this->envFilePath = __DIR__ . '/test.env';
        file_put_contents($this->envFilePath, "KEY1=value1\nKEY2=value2\n");
    }

    protected function tearDown(): void
    {
        // Remove the temporary .env file after testing
        if (file_exists($this->envFilePath)) {
            unlink($this->envFilePath);
        }
    }

    public function testInnerLoader()
    {
        $config = ['file' => $this->envFilePath];
        $loader = ConfigLoader::getInstance(ConfigLoader::ENV_LOADER, $config);
        $data = $loader->innerLoader();

        $this->assertArrayHasKey('KEY1', $data);
        $this->assertEquals('value1', $data['KEY1']);
        $this->assertArrayHasKey('KEY2', $data);
        $this->assertEquals('value2', $data['KEY2']);
    }

    public function testInnerLoaderFileNotConfigured()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('env file not configured');

        $config = [];
        $loader = ConfigLoader::getInstance(ConfigLoader::ENV_LOADER, $config);
        $loader->innerLoader();
    }

    public function testInnerLoaderFileNotFound()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('env file not found');

        $config = ['file' => 'non_existent_file.env'];
        $loader = ConfigLoader::getInstance(ConfigLoader::ENV_LOADER, $config);
        $loader->innerLoader();
    }
}
