<?php

namespace Test\Loader;

use Loader\Exception\LoaderException;
use Loader\Loader;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class LoaderTest extends TestCase
{
    protected $loader;


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
        $this->loader = Loader::intialize();
        $result = $this->loader->loadFile($file);
        $this->assertTrue($result);
        @unlink($file);
    }

    public function testLoadFileFailure()
    {
        $file = __DIR__ . '/fixture/nonexistent.php';
        $this->loader = Loader::intialize();
        $result = $this->loader->loadFile($file);
        $this->assertFalse($result);
    }

    public function testLoadAll()
    {
        $dir = __DIR__ . '/fixture';
        file_put_contents("$dir/test1.php", '<?php class Test1 {}');
        file_put_contents("$dir/test2.php", '<?php class Test2 {}');
        $this->loader = Loader::intialize();
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
            public $load;
        };

        Loader::setPrefixes(['model' => 'Test\\Model\\']);

        // Define the TestModel class in the correct namespace
        eval('class TestModel {}');

        // Call the autoLoadClass method to load the model
        Loader::autoLoadClass($mockCtrl, ['model' => ['test' => 'TestModel']]);

        // Assert that the model was loaded correctly
        $this->assertInstanceOf('TestModel', $mockCtrl->load->model->test);
    }

    public function testModelFailure()
    {
        $this->expectException(LoaderException::class);

        $mockCtrl = new class() {};

        Loader::autoLoadClass($mockCtrl, ['model' => ['NonExistent']]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testServiceSuccess()
    {
        $mockCtrl = new class() {
            public $test;
            public $load;
        };

        eval(' class TestService {}');

        Loader::autoLoadClass($mockCtrl, ['service' => ['test' => 'TestService'], 'test' => ['test']]);
        $this->assertInstanceOf('TestService', $mockCtrl->load->service->test);
    }

    public function testServiceFailure()
    {
        $mockCtrl = new class() {
            public $test;
            public $load;
        };
        $this->expectException(LoaderException::class);
        $this->expectExceptionCode(LoaderException::CLASS_NOT_FOUND_ERROR);

        Loader::intialize();

        Loader::autoLoadClass($mockCtrl, ['service' => ['Test']]);
    }

    public function testLibrarySuccess()
    {
        $mockCtrl = new class() {
            public $load;
            public $test;
        };
        Loader::intialize();
        eval('class Test {}');

        Loader::autoLoadClass($mockCtrl, ['library' => ['Test']]);
        $this->assertInstanceOf('Test', $mockCtrl->load->library->test);
    }

    public function testLibraryFailure()
    {
        $this->expectException(LoaderException::class);

        $mockCtrl = new class() {};

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

        $this->loader = Loader::intialize();
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
        $this->loader = Loader::intialize();
        $this->loader->helpers(['testHelper2']);

        $this->assertTrue(function_exists('testHelper2'));
        $this->assertEquals('Helper Loaded', testHelper2());

        @unlink($helperFile);
    }

    public function testHelperFailure()
    {
        $this->expectException(LoaderException::class);
        $loader = Loader::intialize();
        // Attempt to load a non-existent helper
        $loader->helper('NonExistentHelper');
    }

    public function testSetClassStoresInstanceOnLoad()
    {
        // Create a mock controller with a load property
        $mockCtrl = new class() {
            public $load;
        };

        // Set the static controller property
        Loader::intialize();
        Loader::setPrefixes([
            Loader::MODEL => '',
            Loader::SERVICE => '',
            Loader::LIBRARY => '',
        ]);
        // Set the static controller for Loader
        $ref = new ReflectionClass(Loader::class);
        $ctrlProp = $ref->getProperty('ctrl');
        $ctrlProp->setAccessible(true);
        $ctrlProp->setValue(null, $mockCtrl);

        // Define a dummy class to load
        eval('class DummyModel {}');

        // Call setClass
        $loader = Loader::intialize();
        $loader->setClass('DummyModel', 'dummy', Loader::MODEL);

        // Assert that the DummyModel instance is stored in $mockCtrl->load->model->dummy
        $this->assertInstanceOf('DummyModel', $mockCtrl->load->model->dummy);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSetClassWithoutCtrl()
    {
        $loader = Loader::intialize();
        $this->expectException(LoaderException::class);
        $this->expectExceptionCode(LoaderException::MAPPER_NOT_FORUND_ERROR);
        $loader->setClass(TestClass::class, 'test');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSetClassWithInvalidLoad()
    {
        $mockCtrl = new class() {
            public $load;
        };
        $mockCtrl->load = 10;
        $loader = Loader::intialize();
        $loader->autoLoadClass($mockCtrl, []);
        $this->expectException(LoaderException::class);
        $this->expectExceptionCode(LoaderException::INVALID_LOAD_CLASS_ERROR);
        $loader->setClass(TestClass::class, 'test');
    }
}
