<?php

namespace Test\Loader;

use DateTime;
use Loader\Container;
use Loader\Exception\LoaderException;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use stdClass;

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
        $this->expectException(LoaderException::class);
        $this->expectExceptionMessage('Service not found: nonExistentService');

        // Attempt to retrieve a non-existent service
        Container::get('nonExistentService');
    }

    public function testResolveMethod()
    {
        // Define a test class with a method that has dependencies
        $class = TestMethodClass::class;
        $method = 'testMethod';
        $data = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];

        // Resolve the method dependencies
        $dependencies = Container::resolveMethod($class, $method, $data);

        // Assert that the resolved dependencies match the provided data
        $this->assertCount(2, $dependencies);
        $this->assertEquals('value1', $dependencies['param1']);
        $this->assertEquals('value2', $dependencies['param2']);
    }

    public function testGetClassParamsWithObjects()
    {
        $params = [
            'param1' => new stdClass(),
            'param2' => new DateTime(),
        ];

        $resolvedParams = Container::getClassParams($params);

        $this->assertArrayHasKey('param1', $resolvedParams);
        $this->assertInstanceOf(stdClass::class, $resolvedParams['param1']);
        $this->assertArrayHasKey('param2', $resolvedParams);
        $this->assertInstanceOf(DateTime::class, $resolvedParams['param2']);
    }

    public function testGetClassParamsWithCallables()
    {
        $params = [
            'param1' => function() {
                return 'value1';
            },
            'param2' => fn () => 'value2',
        ];

        $resolvedParams = Container::getClassParams($params);
        $this->assertArrayHasKey('param1', $resolvedParams);
        $this->assertEquals('value1', $resolvedParams['param1']);
        $this->assertArrayHasKey('param2', $resolvedParams);
        $this->assertEquals('value2', $resolvedParams['param2']);
    }

    public function testGetClassParamsWithStrings()
    {
        $params = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];

        $resolvedParams = Container::getClassParams($params);

        $this->assertArrayHasKey('param1', $resolvedParams);
        $this->assertEquals('value1', $resolvedParams['param1']);
        $this->assertArrayHasKey('param2', $resolvedParams);
        $this->assertEquals('value2', $resolvedParams['param2']);
    }

    public function testGetClassParamsWithRegisteredClass()
    {
        Container::set('testService', new stdClass());

        $params = [
            'param1' => 'testService',
        ];

        $resolvedParams = Container::getClassParams($params);

        $this->assertArrayHasKey('param1', $resolvedParams);
        $this->assertInstanceOf(stdClass::class, $resolvedParams['param1']);
    }

    public function testResolveDependencyWithPrimitive()
    {
        $reflection = new ReflectionClass(Container::class);
        $method = $reflection->getMethod('resolveDependency');
        $method->setAccessible(true);

        $paramMock = $this->createMock(ReflectionParameter::class);
        $paramMock->method('getName')->willReturn('param1');
        $data = ['param1' => 'value1'];

        $result = $method->invoke(null, $paramMock, $data);

        $this->assertEquals('value1', $result);
    }

    public function testResolveDependencyWithClass()
    {
        $reflection = new ReflectionClass(Container::class);
        $method = $reflection->getMethod('resolveDependency');
        $method->setAccessible(true);

        $paramMock = $this->createMock(ReflectionParameter::class);
        $typeMock = $this->createMock(ReflectionNamedType::class);
        $typeMock->method('getName')->willReturn('stdClass');
        $typeMock->method('isBuiltin')->willReturn(false);

        $paramMock->method('getType')->willReturn($typeMock);

        $result = $method->invoke(null, $paramMock, []);

        $this->assertInstanceOf(stdClass::class, $result);
    }

    public function testResolveDependencyWithDefaultValue()
    {
        $reflection = new ReflectionClass(Container::class);
        $method = $reflection->getMethod('resolveDependency');
        $method->setAccessible(true);

        $paramMock = $this->createMock(ReflectionParameter::class);
        $paramMock->method('isDefaultValueAvailable')->willReturn(true);
        $paramMock->method('getDefaultValue')->willReturn('default_value');

        $result = $method->invoke(null, $paramMock, []);

        $this->assertEquals('default_value', $result);
    }

    public function testResolveDependencyThrowsException()
    {
        $reflection = new ReflectionClass(Container::class);
        $method = $reflection->getMethod('resolveDependency');
        $method->setAccessible(true);

        $paramMock = $this->createMock(ReflectionParameter::class);
        $paramMock->method('getName')->willReturn('param1');
        $paramMock->method('isDefaultValueAvailable')->willReturn(false);

        $this->assertNull($method->invoke(null, $paramMock, []));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testGetConstrParams()
    {
        // Mock the ReflectionClass
        $reflectionClassMock = m::mock('ReflectionClass');
        $reflectionClassMock->shouldReceive('getConstructor')
            ->once()
            ->andReturnSelf();

        // Mock the ReflectionParameter
        $reflectionParameterMock1 = m::mock('ReflectionParameter');
        $reflectionParameterMock1->shouldReceive('getName')
            ->andReturn('param1');
        $reflectionParameterMock1->shouldReceive('getType')
            ->andReturn(null);

        $reflectionParameterMock2 = m::mock('ReflectionParameter');
        $reflectionParameterMock2->shouldReceive('getName')
            ->andReturn('param2');
        $reflectionParameterMock2->shouldReceive('getType')
            ->andReturn(null);

        // Mock the getParameters method to return an array of ReflectionParameter mocks
        $reflectionClassMock->shouldReceive('getParameters')
            ->once()
            ->andReturn([$reflectionParameterMock1, $reflectionParameterMock2]);

        // Use Reflection to mock the resolveDependency method
        $reflection = new ReflectionClass(Container::class);
        $resolveDependencyMethod = $reflection->getMethod('resolveDependency');
        $resolveDependencyMethod->setAccessible(true);

        // Call the getConstrParams method
        $result = Container::getConstrParams(TestClass::class, ['param1' => 'value1', 'param2' => 'value2']);

        // Assert the resolved dependencies
        $this->assertCount(2, $result);
        $this->assertInstanceOf(TestDependency::class, $result[0]);
        $this->assertEquals('value2', $result[1]);
    }
}

class TestMethodClass
{
    public function testMethod(string $param1, string $param2)
    {
        return $param1 . ' ' . $param2;
    }
}

class TestDependency
{
}
class TestClass
{
    public function __construct(TestDependency $dependency, $param2)
    {
    }
}
