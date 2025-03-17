<?php

namespace Loader\Config;

use Loader\Exception\LoaderException;

class ArrayLoader extends ConfigLoader
{
    /**
     * Loads env file values from .env file and add to $_ENV
     *
     * @return array
     */
    public function innerLoader(): array
    {
        $file = $this->config['file'] ?? '';
        if (empty($file)) {
            throw new LoaderException('env file not configured', LoaderException::FILE_NOT_FOUND_ERROR);
        }
        if (! (file_exists($file))) {
            throw new LoaderException('env file not found : ' . $file, LoaderException::FILE_NOT_FOUND_ERROR);
        }

        return require $file;
    }
}
