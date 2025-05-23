<?php

namespace Loader\Config;

class ArrayLoader extends ConfigLoader
{
    /**
     * Loads env file values from .env file and add to $_ENV
     *
     * @return array
     */
    public function innerLoader(): array
    {
        $file = $this->getFile();

        return require $file;
    }

    /**
     * Return valid file types
     *
     * @return string[]
     */
    public function getValidFileTypes(): array
    {
        return ['php'];
    }
}
