<?php

namespace Loader;

use stdClass;

class Load
{
    /**
     * Stores model classes
     *
     * @var stdClass
     */
    public $model;

    /**
     * Store service classes
     *
     * @var stdClass
     */
    public $service;

    /**
     * Store library classes
     *
     * @var stdClass
     */
    public $library;

    public function addClass($type, $key, $class)
    {
        $this->$type->$key = $class;
    }

    public function __construct()
    {
        $this->model = new stdClass();
        $this->service = new stdClass();
        $this->library = new stdClass();
    }
}
