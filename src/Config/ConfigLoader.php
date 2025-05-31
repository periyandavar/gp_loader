<?php

namespace Loader\Config;

use Loader\Exception\LoaderException;

abstract class ConfigLoader
{
    private static $instances = [];
    protected $settings;

    protected $loadHandler;

    protected $data = [];

    #region settings keys
    public const FILE_NAME = 'file'; // file name

    #region Loader Type Constants
    public const ENV_LOADER = 'env'; // .env file
    public const ARRAY_LOADER = 'array'; // array file
    public const VALUE_LOADER = 'value'; // value file
    public const JSON_LOADER = 'json'; // json file
    public const XML_LOADER = 'xml'; // xml file
    public const YAML_LOADER = 'yaml'; // yaml file
    #endregion

    /**
     * Instantitate the new EnvParser Instance
     *
     * @param array $settings
     *
     */
    protected function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Loads env file values from .env file and handles them based on the load handler
     *
     * @return static
     */
    public function load()
    {
        $this->data = $this->innerLoader();

        $this->loadHandler();

        return $this;
    }

    /**
     * Get the value of a key
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Get all the config values
     *
     * @return array
     */
    public function getAll()
    {
        return $this->data;
    }

    /**
     * Add more data to the config
     *
     * @param array $data
     *
     * @return static
     */
    public function merge(array $data)
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * Set a value to a key
     *
     * @param string $key
     * @param mixed  $value
     * @param bool   $strict if true, it will throw an exception if the key does not exist
     *
     * @return static
     */
    public function set(string $key, $value, bool $strict = false)
    {
        if ($strict && ! isset($this->data[$key])) {
            throw new LoaderException('Key not found : ' . $key, LoaderException::CONFIG_NOT_FOUND_ERROR);
        }

        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Override the config values
     *
     * @param array $data
     *
     * @return static
     */
    public function override(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Handle the loading of the config file
     *
     * @return array
     */
    abstract public function innerLoader(): array;

    /**
     * Return valid file types
     *
     * @return string[]
     */
    abstract public function getValidFileTypes(): array;

    /**
     * Get the instance of the loader
     *
     * @param string $driver
     * @param array  $config
     * @param string $name
     *
     * @return ConfigLoader
     */
    public static function getInstance($driver, $config = [], $name = '')
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
            case self::JSON_LOADER:
                $instance = new JsonLoader($config);

                break;
            case self::XML_LOADER:
                $instance = new XmlLoader($config);

                break;
            case self::YAML_LOADER:
                $instance = new YamlLoader($config);

                break;

            default:
                throw new LoaderException('Driver not found : ' . $driver, LoaderException::LOADER_DRIVER_NOT_FOUND_ERROR);
        }

        if (! empty($name)) {
            self::$instances[$name] = $instance;
        }

        return $instance;
    }

    /**
     * Load the config file
     *
     * @param string $file
     * @param string $name
     *
     * @return ConfigLoader
     */
    public static function loadConfig(string $file, string $name = '', $mode = 'w')
    {
        $file_type = pathinfo($file, PATHINFO_EXTENSION);
        $settings = [
            self::FILE_NAME => $file,
        ];

        if ($mode == 'r' && (self::$instances[$name] ?? false)) {
            return self::$instances[$name];
        }

        switch ($file_type) {
            case 'env':
                $instance = new EnvLoader($settings);

                break;
            case 'php':
                $instance = new ArrayLoader($settings);

                break;
            case 'json':
                $instance = new JsonLoader($settings);

                break;
            case 'xml':
                $instance = new XmlLoader($settings);

                break;
            case 'yaml':
            case 'yml':
                $instance = new YamlLoader($settings);

                break;
            default:
                throw new LoaderException('File type not supported : ' . $file_type, LoaderException::FILE_TYPE_NOT_SUPPORTED_ERROR);
        }

        if (isset(self::$instances[$name]) && $mode == 'a') {
            $config = self::$instances[$name];
            $config->merge($instance->getAll());

            return $config;
        }

        if (! empty($name)) {
            self::$instances[$name] = $instance;
        }

        return $instance->load();
    }

    /**
     * config load handler
     */
    private function loadHandler()
    {
        if ($this->loadHandler) {
            return call_user_func($this->loadHandler, $this->data);
        }
    }

    /**
     * set custom load hanlder.
     *
     * @param callable $_loadHandler
     *
     * @return $this
     */
    public function setLoadHandler(callable $_loadHandler)
    {
        $this->loadHandler = $_loadHandler;

        return $this;
    }

    /**
     * Loads env file values from .env file and add to $_ENV
     *
     * @return void
     */
    public function putToEnv()
    {
        foreach ($this->getAll() as $key => $value) {
            putenv("$key=$value");
        }
    }

    /**
     * Get Config
     *
     * @param  string        $name
     * @return ?ConfigLoader
     */
    public static function getConfig(string $name)
    {
        return self::$instances[$name] ?? null;
    }

    /**
     * Returns the file name to be loaded
     *
     * @throws \Loader\Exception\LoaderException
     */
    public function getFile()
    {
        $file = $this->settings[self::FILE_NAME] ?? '';
        if (empty($file)) {
            throw new LoaderException('File not Configured.', LoaderException::FILE_NOT_FOUND_ERROR);
        }
        if (! (file_exists($file))) {
            throw new LoaderException('File not found : ' . $file, LoaderException::FILE_NOT_FOUND_ERROR);
        }

        $file_type = pathinfo($file, PATHINFO_EXTENSION);
        if (! in_array($file_type, $this->getValidFileTypes())) {
            throw new LoaderException('File type is not supported ' . static::class . ' : {' . $file_type . '} valid types are { ' . implode($this->getValidFileTypes()) . '} ', LoaderException::FILE_TYPE_NOT_SUPPORTED_ERROR);
        }

        return $file;
    }
}
