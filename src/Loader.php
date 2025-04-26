<?php

/**
 * Loader
 * php version 7.3.5
 *
 * @category Loader
 * @package  Core
 * @author   Periyandavar <periyandavar@gmail.com>
 * @license  http://license.com license
 * @link     http://url.com
 */

namespace Loader;

use Loader\Config\ConfigLoader;
use Loader\Exception\LoaderException;

/**
 * Loader Class autoloads the files
 *
 * @category Loader
 * @package  Core
 * @author   Periyandavar <periyandavar@gmail.com>
 * @license  http://license.com license
 * @link     http://url.com
 */
class Loader
{
    /**
     * Loader class instance
     *
     * @var Loader|null
     */
    protected static $instance = null;

    protected static $prefixes = [];
    /**
     * Controller object
     *
     * @var object
     */
    protected static $ctrl;

    /**
     * Instantiate the the Loader instance
     */
    private function __construct(?ConfigLoader $_config = null)
    {
        $default_config = [
            'model' => "App\Model\\",
            'service' => "App\Service\\",
            'helper' => "App\Helper\\",
            'library' => "System\Library\\",
        ];
        $config = $_config ? $_config->getAll() : $default_config;
        $config = array_merge($default_config, $config);

        foreach ($config as $key => $value) {
            self::$prefixes[$key] = $value;
        }
    }

    public static function setPrefixes(array $prefixes)
    {
        self::$prefixes = $prefixes;
    }

    /**
     * Loads the all classes from autoload class list
     * and creates the instance for them
     *
     * @param object $ctrl Controller object
     *
     * @return Loader
     */
    public static function autoLoadClass($ctrl, $autoloads, ?ConfigLoader $config = null): Loader
    {
        $loads = array_keys(self::$prefixes);
        static::$instance ?? static::intialize($config);
        static::$ctrl = $ctrl;
        foreach ($loads as $load) {
            $files = $autoloads[$load] ?? [];
            if (! is_array($files)) {
                $files = [$files];
            }

            static::$instance->$load(...$files);
        }

        // exit;
        return static::$instance;
    }

    /**
     * Loads models
     *
     * @param string ...$models Model list
     *
     * @return void
     */
    public function model(...$models)
    {
        $ns = self::$prefixes['model'];
        foreach ($models as $model) {
            $class = $ns . $model . 'Model';
            if (class_exists($class)) {
                static::$ctrl->{lcfirst($model)} = new $class();
            } else {
                throw new LoaderException(
                    "Unable to locate the model class '$model'",
                    LoaderException::CLASS_NOT_FOUND_ERROR
                );
            }
        }
    }

    /**
     * Loads Services
     *
     * @param string ...$services Service list
     *
     * @return void
     * @throws LoaderException
     */
    public function service(...$services)
    {
        $ns = self::$prefixes['service'];
        foreach ($services as $service) {
            $class = $ns . $service . 'Service';
            if (class_exists($class)) {
                static::$ctrl->{lcfirst($service)} = new $class();
            } else {
                throw new LoaderException(
                    "Unable to loacate the '$service' class [$class]",
                    LoaderException::CLASS_NOT_FOUND_ERROR
                );
            }
        }
    }

    /**
     * Loads Libraries
     *
     * @param string ...$libraries Library list
     *
     * @return void
     * @throws LoaderException
     */
    public function library(...$libraries)
    {
        $ns = self::$prefixes['library'];
        foreach ($libraries as $library) {
            $lib_class = $ns . $library;
            if (class_exists($lib_class)) {
                static::$ctrl->{lcfirst($library)} = new $lib_class();
            } else {
                throw new LoaderException(
                    "Library class '$library' not found [$lib_class]",
                    LoaderException::CLASS_NOT_FOUND_ERROR
                );
            }
        }
    }

    /**
     * Loads helpers
     *
     * @param string ...$helpers Helper list
     *
     * @return void
     * @throws LoaderException
     */
    public function helper(...$helpers)
    {
        foreach ($helpers as $helper) {
            $helper_file = trim(rtrim(self::$prefixes['helper'], '\\') . '/' . $helper) . '.php';

            if (file_exists($helper_file)) {
                include_once $helper_file;

                continue;
            }

            throw new LoaderException("Helper class '$helper' not found [$helper_file]", LoaderException::CLASS_OR_FILE_NOT_FOUND_ERROR);
        }
    }

    /**
     * Loads all php files from the specified directory
     *
     * @param string $dir Directory Name
     *
     * @return void
     */
    public static function loadAll(string $dir)
    {
        foreach (glob("$dir/*.php") as $filename) {
            include_once $filename;
        }
    }

    /**
     * Includes the file if it exists
     *
     * @param string $file file name
     *
     * @return bool
     */
    public function loadFile(string $file): bool
    {
        $file = rtrim($file, '.php') . '.php';

        if (file_exists($file)) {
            include_once $file;

            return true;
        }

        return false;
    }

    /**
     * Intialize the Loader class and returns load class object
     *
     * @return Loader
     */
    public static function intialize(?ConfigLoader $config = null): Loader
    {
        if (self::$instance == null) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }
}
