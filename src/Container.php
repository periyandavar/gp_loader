<?php

namespace Loader;

use Exception;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use System\Core\Log;

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
     *
     * @return mixed
     */
    private static function resolveDependency(ReflectionParameter $_param)
    {
        $type = $_param->getType();

        if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
            $name = $type->getName();

            return self::isClassRegistered($name) ? self::get($name) : self::resolve($name);
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
        $service = self::$services[$_name] ?? null;

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
            throw new Exception("Service not found: {$_name}");
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
    public static function resolve(string $_class_name)
    {
        $reflection = new ReflectionClass($_class_name);
        if ($reflection->isAbstract() || $reflection->isInterface()) {
            // Log::getInstance()->info("{$_class_name} is abstract or interface");

            return null;
        }

        $constructor = $reflection->getConstructor();

        $params = $constructor ? $constructor->getParameters() : [];

        $dependencies = [];
        foreach ($params as $param) {
            $dependencies[] = self::resolveDependency($param);
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Load the configuration.
     *
     * @param array $_config
     */
    public static function loadFromConfig(array $_config)
    {
        foreach ($_config as $name => $config) {
            $singleton = $config['singleton'] ?? false;
            $closure = function() use ($config) {
                if ($config['params']) {
                    $params = self::getClassParams($config['params']);

                    return new $config['class'](...$params);
                }

                return new $config['class']();
            };
            self::set($name, $closure, $singleton);
        }
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
            if (is_object($value)) {
                $params[$param] = $value;

                continue;
            }

            if (is_callable($value)) {
                $params[$param] = $value();

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

            $_params[$param] = $value;
        }

        return $params;
    }
}
