<?php

namespace Loader\Config;

use Loader\Exception\LoaderException;

class XmlLoader extends ConfigLoader
{
    /**
     * Loads env file values from .env file and add to $_ENV
     *
     * @return array
     */
    public function innerLoader(): array
    {
        $file = $this->getFile();

        $data = [];
        $xmlObject = @simplexml_load_file($file);
        if ($xmlObject === false) {
            throw new LoaderException('Unable to read file : ' . $file, LoaderException::FILE_READ_ERROR);
        }
        $data = json_decode(json_encode($xmlObject), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new LoaderException('XML decode error: ' . json_last_error_msg(), LoaderException::FILE_READ_ERROR);
        }

        return $data;
    }

    /**
     * Return valid file types
     *
     * @return string[]
     */
    public function getValidFileTypes(): array
    {
        return ['xml'];
    }
}
