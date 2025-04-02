<?php

namespace Loader;

use Loader\Exception\LoaderException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

class Container
{
    private static $instances = [];
    private static $services = [];

    /**
     * Set the instance and service in the container.
     *
     * @param string $_name
     * @param mixed  $_closure
     * @param bool   $_singleton
     */
    public static function set(string $_name, mixed $_closure, bool $_singleton = false)
    {
        $name = strtolower($_name);
        if ($_singleton) {
            self::$instances[$name] = $_closure;
        } else {
            self::$services[$name] = $_closure;
        }
    }

    /**
     * Resolve the dependency.
     *
     * @param ReflectionParameter $_param
     * @param array               $data
     *
     * @return mixed
     */
    public static function resolveDependency(ReflectionParameter $_param, array $data = [])
    {
        $type = $_param->getType();

        $param_name = $_param->getName();
        if (isset($data[$param_name])) {
            return $data[$param_name];
        }

        if ($type instanceof ReflectionNamedType) {
            $name = $type->getName();
            if (isset($data[$name])) {
                return $data[$name];
            }

            if (! $type->isBuiltin()) {
                return self::isClassRegistered($name) ? self::get($name) : self::resolve($name);
            }
        }

        if ($_param->isDefaultValueAvailable()) {
            return $_param->getDefaultValue();
        }

        return null;
    }

    /**
     * Return the instance.
     *
     * @param string $_name
     */
    public static function getInstance(string $_name)
    {
        $instance = self::$instances[$_name] ?? null;

        if (is_callable($instance)) {
            $instance = self::$instances[$_name]();
            self::$instances[$_name] = $instance;
        }

        return $instance;
    }

    /**
     * Return the service.
     *
     * @param string $_name
     */
    public static function getService(string $_name)
    {
        $name = strtolower($_name);
        $service = self::$services[$name] ?? null;

        if (is_callable($service)) {
            return $service();
        }

        return $service;
    }

    /**
     * Get the object from the container.
     *
     * @param string $_name
     *
     * @return mixed
     */
    public static function get(string $_name)
    {
        $name = strtolower($_name);
        $object = self::getInstance($name) ?? self::getService($_name);

        if (is_null($object)) {
            throw new LoaderException("Service not found: {$_name}", LoaderException::CLASS_NOT_FOUND_ERROR);
        }

        return $object;
    }

    /**
     * Check if the class is registered.
     *
     * @param string $_name
     *
     * @return bool
     */
    public static function isClassRegistered(string $_name)
    {
        $name = strtolower($_name);

        return isset(self::$instances[$name]) || isset(self::$services[$name]);
    }

    /**
     * Resolve the class.
     *
     * @param string $_class_name
     *
     * @return mixed
     */
    public static function resolve(string $_class_name, array $data = [])
    {
        $reflection = new ReflectionClass($_class_name);
        $dependencies = self::getConstrParams($_class_name, $data);

        return $reflection->newInstanceArgs($dependencies);
    }

    public static function getConstrParams(string $_class_name, $data)
    {
        $reflection = new ReflectionClass($_class_name);
        if ($reflection->isAbstract() || $reflection->isInterface()) {
            return null;
        }

        $constructor = $reflection->getConstructor();

        $params = $constructor ? $constructor->getParameters() : [];

        $dependencies = [];
        foreach ($params as $param) {
            $dependencies[] = self::resolveDependency($param, $data);
        }

        return $dependencies;
    }

    /**
     * Load the configuration.
     *
     * @param array $_config
     */
    public static function loadFromConfig(array $_config)
    {
        foreach ($_config as $name => $config) {
            if (is_string($config)) {
                $config = ['class' => $config];
            }
            if (is_callable($config)) {
                self::set($name, $config);

                continue;
            }
            $class = $config['class'] ?? $config[0] ?? '';
            if (empty($class)) {
                continue;
            }
            if (is_numeric($name)) {
                $name = $class;
            }
            $singleton = $config['singleton'] ?? false;
            $closure = function() use ($config, $class) {
                if (isset($config['params'])) {
                    $params = self::getClassParams($config['params']);

                    return new $class(...$params);
                }

                return self::resolve($class);
            };
            self::set($name, $closure, $singleton);
        }

        return self::class;
    }

    /**
     * Get the class params.
     *
     * @param array $_params
     *
     * @return array
     */
    public static function getClassParams(array $_params)
    {
        $params = [];

        foreach ($_params as $param => $value) {
            if (is_callable($value)) {
                $params[$param] = call_user_func($value);

                continue;
            }

            if (is_object($value)) {
                $params[$param] = $value;

                continue;
            }

            if (! is_string($value)) {
                $params[$param] = $value;

                continue;
            }

            if (str_starts_with(strtoupper($value), '\s')) {
                $params[$param] = $value;

                continue;
            }

            if (self::isClassRegistered($value)) {
                $params[$param] = self::get($value);

                continue;
            }

            $params[$param] = $value;
        }

        return $params;
    }

    public static function resolveMethod(string $class, string $method, array $data)
    {
        $reflection = new ReflectionClass($class);
        $method = $reflection->getMethod($method);
        $params = $method->getParameters();

        $dependencies = [];
        foreach ($params as $param) {
            $dependencies[$param->getName()] = self::resolveDependency($param, $data);
        }

        return $dependencies;
    }
}
