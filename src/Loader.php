<?php

namespace Loader;

use Loader\Exception\LoaderException;

class Loader
{
    public const MODEL = 'model';
    public const SERVICE = 'service';
    public const HELPER = 'helper';
    public const LIBRARY = 'library';

    public const MODEL_ACTION = 'models';
    public const SERVICE_ACTIOIN = 'services';
    public const HELPER_ACTION = 'helpers';
    public const LIBRARY_ACTION = 'libraries';

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
     * @var object|null
     */
    protected static $ctrl = null;

    /**
     * Instantiate the the Loader instance
     */
    private function __construct()
    {
    }

    /**
     * Set the prefixes for the loader
     *
     * @param array $prefixes Prefixes for model, service, helper and library
     */
    public static function setPrefixes(array $prefixes)
    {
        self::$prefixes = $prefixes;
    }

    /**
     * Returns the loader map
     *
     * @return string[]
     */
    public static function loaderMap()
    {
        return [
            self::MODEL => self::MODEL_ACTION,
            self::SERVICE => self::SERVICE_ACTIOIN,
            self::HELPER => self::HELPER_ACTION,
            self::LIBRARY => self::LIBRARY_ACTION,
        ];
    }

    /**
     * Checks if the loader is valid
     *
     * @param string $loader Loader name
     *
     * @return bool
     */
    public static function isValidLoader(string $loader): bool
    {
        return in_array($loader, array_keys(self::loaderMap()));
    }

    /**
     * Returns the action name for the loader
     *
     * @param string $loader Loader name
     *
     * @return string|null
     */
    public static function getLoaderAction(string $loader): ?string
    {
        return self::loaderMap()[$loader] ?? null;
    }

    /**
     * Loads the all classes from autoload class list
     * and creates the instance for them
     *
     * @param object $ctrl      Controller object
     * @param array  $autoloads
     *
     * @return Loader
     */
    public static function autoLoadClass($ctrl, array $autoloads): Loader
    {
        if (static::$instance == null) {
            static::$instance = new self();
        }
        static::$ctrl = $ctrl;
        foreach ($autoloads as $key => $load) {
            if (! self::isValidLoader($key)) {
                continue;
            }
            $load = is_array($load) ? $load : [$load];
            $action = self::getLoaderAction($key);
            static::$instance->$action($load);
        }

        return static::$instance;
    }

    /**
     * Load model
     *
     * @param string $model
     * @param string $key
     *
     * @return void
     */
    public function model(string $model, string $key)
    {
        $this->setClass($model, $key, self::MODEL);
    }

    /**
     * Loads models
     *
     * @param array $models Model list
     *
     * @return void
     */
    public function models(array $models)
    {
        foreach ($models as $key => $model) {
            $this->model($model, $key);
        }
    }

    /**
     * Sets the class for the loader
     *
     * @param string $class Class name
     * @param string $key   Key for the class
     * @param string $type  Type of the class (service, model, library, helper)
     *
     * @return void
     * @throws LoaderException
     */
    public function setClass(string $class, string $key, string $type = self::SERVICE)
    {
        if (is_null(static::$ctrl)) {
            throw new LoaderException(
                'Mapper object is not set for the loader',
                LoaderException::MAPPER_NOT_FORUND_ERROR
            );
        }
        $load = static::$ctrl->load ?? new Load();
        if (! $load instanceof Load) {
            throw new LoaderException(
                'Load object is not set for the loader',
                LoaderException::INVALID_LOAD_CLASS_ERROR
            );
        }
        $ns = self::$prefixes[$type] ?? '';
        $key = $this->generateKey($key, $class);
        $class = $this->getClass($class, $ns);
        $load->$type->$key = $class;
        static::$ctrl->load = $load;
    }

    /**
     * Generates a key for the class
     *
     * @param string $key   Key for the class
     * @param string $class Class name
     *
     * @return string
     */
    public function generateKey(string $key, string $class): string
    {
        if (! (empty($key)) && ! (is_numeric($key))) {
            return $key;
        }

        return strtolower(basename(str_replace('\\', '/', $class)));
    }

    /**
     * Returns the class object for the given class name
     *
     * @param string $class     Class name
     * @param string $namespace Namespace for the class
     *
     * @return object
     * @throws LoaderException
     */
    public function getClass(string $class, string $namespace)
    {
        $obj = null;

        try {
            $obj = self::loadClass($class);
        } catch (LoaderException) {
            $class = $namespace . '\\' . $class;
            $obj = self::loadClass($class);
        }

        return $obj;
    }

    /**
     * Loads the class and returns the instance of the class
     *
     * @param  string                            $class
     * @throws \Loader\Exception\LoaderException
     *
     * @return object
     */
    public static function loadClass(string $class)
    {
        if (! class_exists($class)) {
            throw new LoaderException(
                "Unable to locate the model class '$class'",
                LoaderException::CLASS_NOT_FOUND_ERROR
            );
        }

        return Container::resolveClassConstructor($class);
    }

    /**
     * Loads Service
     *
     * @param string $service Service
     * @param string $key
     *
     * @return void
     * @throws LoaderException
     */
    public function service(string $service, string $key)
    {
        $this->setClass($service, $key, self::SERVICE);
    }

    /**
     * Load the services.
     *
     * @param  array $services
     * @return void
     */
    public function services(array $services)
    {
        foreach ($services as $key => $service) {
            $this->service($service, $key);
        }
    }

    /**
     * Loads Libraries
     *
     * @param array $libraries Library list
     *
     * @return void
     * @throws LoaderException
     */
    public function libraries(array $libraries)
    {
        foreach ($libraries as $key => $library) {
            $this->library($library, $key);
        }
    }

    /**
     * Loads a library
     *
     * @param string $library Library name
     * @param string $key     Key for the library
     *
     * @return void
     * @throws LoaderException
     */
    public function library(string $library, string $key)
    {
        $this->setClass($library, $key, self::LIBRARY);
    }

    /**
     * Loads helpers
     *
     * @param array $helpers Helper list
     *
     * @return void
     * @throws LoaderException
     */
    public function helpers(array $helpers)
    {
        foreach ($helpers as $helper) {
            $this->helper($helper);
        }
    }

    /**
     * Loads a helper
     *
     * @param string $helper Helper name
     *
     * @return void
     * @throws LoaderException
     */
    public function helper(string $helper)
    {
        $helper_file = trim(rtrim(self::$prefixes['helper'] ?? '', '\\') . '/' . $helper) . '.php';

        if (! $this->loadFile($helper_file)) {
            throw new LoaderException('Loader file not exists', LoaderException::FILE_NOT_FOUND_ERROR);
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
     * @return self
     */
    public static function intialize(): Loader
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
