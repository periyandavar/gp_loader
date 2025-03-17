<?php

namespace Loader\Config;

use Loader\Exception\LoaderException;

class EnvLoader extends ConfigLoader
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
        $data = [];
        $contents = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($contents as $line) {
            if (strpos(trim($line), '#') !== false) {
                continue;
            }
            list($key, $value) = explode('=', $line, 2);

            // Create the array
            $data[$key] = $value;
        }

        return $data;
    }
}
