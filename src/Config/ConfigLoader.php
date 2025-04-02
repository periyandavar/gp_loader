<?php

/**
 * EnvParser
 * php version 7.3.5
 *
 * @category EnvParser
 * @package  Core
 * @author   Periyandavar <periyandavar@gmail.com>
 * @license  http://license.com license
 * @link     http://url.com
 */

namespace Loader\Config;

use Loader\Exception\LoaderException;

/**
 * EnvParser parse the env files and loads values from it
 *
 * @category EnvParser
 * @package  Core
 * @author   Periyandavar <periyandavar@gmail.com>
 * @license  http://license.com license
 * @link     http://url.com
 */
abstract class ConfigLoader
{
    private static $instances = [];
    protected $config;

    protected $loadHandler;

    protected $data = [];

    public const ENV_LOADER = 'env';
    public const ARRAY_LOADER = 'array';
    public const VALUE_LOADER = 'value';

    /**
     * Instantitate the new EnvParser Instance
     *
     * @param $file ENV File Name
     *
     */
    protected function __construct($config)
    {
        $this->config = $config;
    }

    public function load()
    {
        $this->data = $this->innerLoader();

        $this->loadHandler();

        return $this;
    }

    public function get(string $key)
    {
        return $this->data[$key] ?? null;
    }

    public function getAll()
    {
        return $this->data;
    }

    public function merge(array $data)
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    public function set(string $key, $value, bool $strict = false)
    {
        if ($strict && ! isset($this->data[$key])) {
            throw new LoaderException('Key not found : ' . $key, LoaderException::CONFIG_NOT_FOUND_ERROR);
        }

        $this->data[$key] = $value;

        return $this;
    }

    public function override(array $data)
    {
        $this->data = $data;

        return $this;
    }

    abstract public function innerLoader(): array;

    public static function getInstance($driver, $config = [], $name = ''): ConfigLoader
    {
        switch ($driver) {
            case self::ENV_LOADER:
                $instance = new EnvLoader($config);

                break;
            case self::ARRAY_LOADER:
                $instance = new ArrayLoader($config);

                break;
            case self::VALUE_LOADER:
                $instance = new ValueLoader($config);

                break;
            default:
                throw new LoaderException('Driver not found : ' . $driver, LoaderException::LOADER_DRIVER_NOT_FOUND_ERROR);
        }

        if (! empty($name)) {
            self::$instances[$name] = $instance;
        }

        return $instance;
    }

    private function loadHandler()
    {
        if ($this->loadHandler) {
            return call_user_func($this->loadHandler, $this->data);
        }

        $this->defaultHandler($this->data);
    }

    public function setLoadHandler(callable $_loadHandler)
    {
        $this->loadHandler = $_loadHandler;
    }

    /**
     * Loads env file values from .env file and add to $_ENV
     *
     * @return void
     */
    protected function defaultHandler($data)
    {
        foreach ($data as $key => $value) {
            putenv("$key=$value");
        }
    }

    /**
     * Get Config
     *
     * @param  string       $name
     * @return ConfigLoader
     */
    public static function getConfig(string $name)
    {
        return self::$instances[$name];
    }
}
