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

use Exception;

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
    protected $config;

    protected $loadHandler;

    protected $data = [];

    public const ENV_LOADER = 'env';
    public const ARRAY_LOADER = 'array';

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
    }

    public function set(string $key, $value, bool $strict = false)
    {
        if ($strict && !isset($this->data[$key])) {
            throw new Exception('Key not found : ' . $key);
        }

        $this->data[$key] = $value;
    }

    public function override(array $data)
    {
        $this->data = $data;
    }

    abstract public function innerLoader(): array;

    public static function getInstance($driver, $config = [])
    {
        switch ($driver) {
            case self::ENV_LOADER:
                return new EnvLoader($config);
            case self::ARRAY_LOADER:
                return new ArrayLoader($config);
            default:
                throw new Exception('Driver not found : ' . $driver);
        }
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
}
