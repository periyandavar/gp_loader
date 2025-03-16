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

use Exception;
use Loader\Config\ArrayLoader;
use Loader\Config\ConfigLoader;
use PSpell\Config;
use System\Core\FrameworkException;
use System\Core\SysController;
use System\Core\Utility;

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
     * @var Loader|null $instance
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

        $this->loadAll('src/app/config/routes');
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
    public static function autoLoadClass($ctrl, $autoloads): Loader
    {
        $loads = array_keys(self::$prefixes);
        static::$instance ?? static::intialize();
        static::$ctrl = $ctrl;
        foreach ($loads as $load) {
            $files = $autoloads[$load] ?? [];
            if (! is_array($files)) {
                $files = [$files];
            }
            static::$instance->$load(...$files);
        }

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
                throw new Exception(
                    "Unable to locate the model class '$model'"
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
     * @throws Exception
     */
    public function service(...$services)
    {
        $ns = self::$prefixes['service'];
        foreach ($services as $service) {
            $class = $ns . $service . 'Service';
            if (class_exists($class)) {
                static::$ctrl->{lcfirst($service)} = new $class();
            } else {
                throw new Exception(
                    "Unable to loacate the '$service' class [$class]"
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
     * @throws Exception
     */
    public function library(...$libraries)
    {
        $ns = self::$prefixes['library'];
        foreach ($libraries as $library) {
            $sys_lib_class = 'System\\Library\\' . $library;
            $cust_lib_class = $ns . $library;
            if (class_exists($sys_lib_class)) {
                static::$ctrl->{lcfirst($library)} = new $sys_lib_class();
            } elseif (class_exists($cust_lib_class)) {
                static::$ctrl->{lcfirst($library)} = new $cust_lib_class();
            } else {
                throw new Exception("Library class '$library' not found [$sys_lib_class, $cust_lib_class]");
            }
        }
    }

    /**
     * Loads helpers
     *
     * @param string ...$helpers Helper list
     *
     * @return void
     * @throws Exception
     */
    public function helper(...$helpers)
    {
        foreach ($helpers as $helper) {
            // $helper_file = '/src/system/helper/' . $helper . '.php';
            $helper_file = trim(rtrim(self::$prefixes['helper'], '\\') . '/' . $helper) . '.php';
            $helper_class = self::$prefixes['helper'] . $helper;
            if (class_exists($helper_class)) {
                //
            } elseif (file_exists($helper_file)) {
                include_once $helper_file;
            } else {
                throw new Exception("Helper class '$helper' not found [$helper_class, $helper_file]");
            }
        }
    }

    /**
     * Loads all php files from the specified directory
     *
     * @param string $dir Directory Name
     *
     * @return void
     */
    public function loadAll(string $dir)
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
    public static function intialize(): Loader
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
