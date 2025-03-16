<?php

namespace Test\Loader;

use Exception;
use Loader\Container;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    protected function setUp(): void
    {
        // Clear the container before each test
        $reflection = new \ReflectionClass(Container::class);
        $instances = $reflection->getProperty('instances');
        $instances->setAccessible(true);
        $instances->setValue([]);

        $services = $reflection->getProperty('services');
        $services->setAccessible(true);
        $services->setValue([]);
    }

    public function testSetAndGetInstance()
    {
        // Register an instance
        $instance = new \stdClass();
        Container::set('testInstance', $instance, true);

        // Retrieve the instance
        $retrievedInstance = Container::get('testInstance');
        $this->assertSame($instance, $retrievedInstance);
    }

    public function testSetAndGetService()
    {
        // Register a service
        Container::set('service', function() {
            return new \stdClass();
        });

        // Retrieve the service
        $service = Container::get('service');
        $this->assertInstanceOf(\stdClass::class, $service);
    }

    public function testResolveClass()
    {
        // Define a test class with dependencies
        eval('
            namespace Test\Loader;
            class TestDependency {}
            class TestClass {
                public function __construct(TestDependency $dependency) {}
            }
        ');

        // Resolve the class
        $resolvedClass = Container::resolve('Test\Loader\TestClass');
        $this->assertInstanceOf('Test\Loader\TestClass', $resolvedClass);
    }

    public function testIsClassRegistered()
    {
        // Register a service
        Container::set('testService', function() {
            return new \stdClass();
        });

        // Check if the class is registered
        $this->assertTrue(Container::isClassRegistered('testService'));
        $this->assertFalse(Container::isClassRegistered('nonExistentService'));
    }

    public function testLoadFromConfig()
    {
        // Define a configuration array
        $config = [
            'testService' => [
                'class' => \stdClass::class,
                'singleton' => true,
                'params' => [],
            ],
        ];

        // Load the configuration
        Container::loadFromConfig($config);

        // Retrieve the service
        $service = Container::get('testService');
        $this->assertInstanceOf(\stdClass::class, $service);
    }

    public function testGetNonExistentService()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Service not found: nonExistentService');

        // Attempt to retrieve a non-existent service
        Container::get('nonExistentService');
    }
}
