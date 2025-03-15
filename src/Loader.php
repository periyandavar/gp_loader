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
use System\Core\FrameworkException;
use System\Core\SysController;
use System\Core\Utility;

defined('VALID_REQ') or exit('Invalid request');
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
     * @var Loader|null $_instance
     */
    protected static $_instance = null;

    private $_prefixes = [];
    /**
     * Controller object
     *
     * @var object
     */
    protected static $_ctrl;

    /**
     * Instantiate the the Loader instance
     */
    private function __construct()
    {
        global $config;
        $this->_prefixes = [
            'model' => $config['model'] ?? "App\Model\\",
            'service' => $config['service'] ?? "App\Service\\",
            'helper' => $config['helper'] ?? "App\Helper\\",
            'library' => $config['library'] ?? "System\Library\\",
        ];

        $this->loadAll('src/app/config/routes');
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
        global $autoload;
        $autoloads = $autoloads ?? $autoload;
        $loads = ['model', 'service', 'library', 'helper'];
        static::$_instance ?? static::intialize();
        static::$_ctrl = $ctrl;
        foreach ($loads as $load) {
            $files = $autoloads[$load];
            if (! is_array($files)) {
                $files = [$files];
            }
            static::$_instance->$load(...$files);
        }

        return static::$_instance;
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
        $ns = $this->_prefixes['model'];
        foreach ($models as $model) {
            $class = $ns . $model . 'Model';
            if (class_exists($class)) {
                static::$_ctrl->{lcfirst($model)} = new $class();
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
        $ns = $this->_prefixes['service'];
        foreach ($services as $service) {
            $class = $ns . $service . 'Service';
            if (class_exists($class)) {
                static::$_ctrl->{lcfirst($service)} = new $class();
            } else {
                throw new Exception(
                    "Unable to loacate the '$service' class"
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
        $ns = $this->_prefixes['library'];
        foreach ($libraries as $library) {
            $sys_lib_class = 'System\\Library\\' . $library;
            $cust_lib_class = $ns . $library;
            if (class_exists($sys_lib_class)) {
                static::$_ctrl->{lcfirst($library)} = new $sys_lib_class();
            } elseif (class_exists($cust_lib_class)) {
                static::$_ctrl->{lcfirst($library)} = new $cust_lib_class();
            } else {
                throw new Exception("Library class '$library' not found");
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
            $helper_file = '/src/system/helper/' . $helper . '.php';
            if (class_exists($this->_prefixes['helper'] . $helper)) {
                //
            } elseif (file_exists($helper_file)) {
                include_once $helper_file;
            } else {
                throw new Exception("Helper class '$helper' not found");
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
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
}
