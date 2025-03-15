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

    protected $load_handler;

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
        $data = $this->innerLoader();
        $this->loadHandler($data);
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

    private function loadHandler($data)
    {
        if ($this->load_handler) {
            return call_user_func($this->load_handler, $data);
        }

        $this->defaultHandler($data);
    }

    public function setLoadHandler(callable $_load_handler)
    {
        $this->load_handler = $_load_handler;
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
