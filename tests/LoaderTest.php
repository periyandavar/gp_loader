<?php

namespace Test\Loader;

use Loader\Exception\LoaderException;
use Loader\Loader;
use PHPUnit\Framework\TestCase;

class LoaderTest extends TestCase
{
    protected $loader;

    protected function setUp(): void
    {
        $this->loader = Loader::intialize();
    }

    public function testIntialize()
    {
        $instance = Loader::intialize();
        $this->assertInstanceOf(Loader::class, $instance);
    }

    public function testLoadFileSuccess()
    {
        $file = __DIR__ . '/fixture/sample.php';
        if (! is_dir(__DIR__ . '/fixture')) {
            mkdir(__DIR__ . '/fixture');
        }
        file_put_contents($file, '<?php return true;');
        $result = $this->loader->loadFile($file);
        $this->assertTrue($result);
        @unlink($file);
    }

    public function testLoadFileFailure()
    {
        $file = __DIR__ . '/fixture/nonexistent.php';
        $result = $this->loader->loadFile($file);
        $this->assertFalse($result);
    }

    public function testLoadAll()
    {
        $dir = __DIR__ . '/fixture';
        file_put_contents("$dir/test1.php", '<?php class Test1 {}');
        file_put_contents("$dir/test2.php", '<?php class Test2 {}');

        $this->loader->loadAll($dir);

        $this->assertTrue(class_exists('Test1'));
        $this->assertTrue(class_exists('Test2'));

        unlink("$dir/test1.php");
        unlink("$dir/test2.php");
    }

    public function testModelSuccess()
    {
        $mockCtrl = new class() {
            public $test;
        };

        Loader::intialize(); // Ensure the Loader is initialized
        Loader::setPrefixes(['model' => 'Test\\Model\\']);

        // Define the TestModel class in the correct namespace
        eval('namespace Test\Model; class TestModel {}');

        // Call the autoLoadClass method to load the model
        Loader::autoLoadClass($mockCtrl, ['model' => 'Test']);

        // Assert that the model was loaded correctly
        $this->assertInstanceOf('Test\Model\TestModel', $mockCtrl->test);
    }

    public function testModelFailure()
    {
        $this->expectException(LoaderException::class);
        $this->expectExceptionMessage("Unable to locate the model class 'NonExistent'");

        $mockCtrl = new class() {};
        Loader::setPrefixes(['model' => 'Test\\Model\\']);

        Loader::autoLoadClass($mockCtrl, ['model' => ['NonExistent']]);
    }

    public function testServiceSuccess()
    {
        $mockCtrl = new class() {
            public $test;
        };

        Loader::intialize();
        Loader::setPrefixes(['service' => 'Test\\Service\\']);
        eval('namespace Test\Service; class TestService {}');

        Loader::autoLoadClass($mockCtrl, ['service' => ['Test']]);
        $this->assertInstanceOf('Test\Service\TestService', $mockCtrl->test);
    }

    public function testLibrarySuccess()
    {
        $mockCtrl = new class() {
            public $test;
        };
        Loader::intialize();
        Loader::setPrefixes(['library' => 'Test\\Library\\']);
        eval('namespace Test\Library; class Test {}');

        Loader::autoLoadClass($mockCtrl, ['library' => ['Test']]);
        $this->assertInstanceOf('Test\Library\Test', $mockCtrl->test);
    }

    public function testLibraryFailure()
    {
        $this->expectException(LoaderException::class);
        $this->expectExceptionMessage("Library class 'NonExistent' not found");

        $mockCtrl = new class() {};
        Loader::setPrefixes(['library' => 'Test\\Library\\']);

        Loader::autoLoadClass($mockCtrl, ['library' => ['NonExistent']]);
    }

    public function testHelperSuccess()
    {
        $dir = __DIR__ . '/fixture';
        if (! is_dir($dir)) {
            mkdir($dir);
        }
        $helperFile = "$dir/testHelper1.php";
        file_put_contents($helperFile, '<?php function testHelper() {}');

        Loader::setPrefixes(['helper' => $dir . '\\']);
        $this->loader->loadFile($helperFile);

        $this->assertTrue(condition: function_exists('testHelper'));

        @unlink($helperFile);
    }

    public function testHelperMethodSuccess()
    {
        $dir = __DIR__ . '/fixture';
        $helperFile = "$dir/testHelper2.php";
        file_put_contents($helperFile, '<?php function testHelper2() { return "Helper Loaded"; }');

        Loader::setPrefixes(['helper' => $dir . '\\']);

        $this->loader->helper('testHelper2');

        $this->assertTrue(function_exists('testHelper2'));
        $this->assertEquals('Helper Loaded', testHelper2());

        @unlink($helperFile);
    }

    public function testHelperFailure()
    {
        $this->expectException(LoaderException::class);
        $this->expectExceptionMessage("Helper class 'NonExistentHelper' not found");

        // Attempt to load a non-existent helper
        $this->loader->helper('NonExistentHelper');
    }
}
