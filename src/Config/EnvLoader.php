<?php

/**
 * EnvParser
 * php version 7.3.5
 *
 * @category EnvParser
 * @package  Core
 * @author   Periyandavar <periyandavar@gmail.com>
 * @license  http://license.com license
 * @link     http://url.com
 */

namespace Loader\Config;

/**
 * EnvParser parse the env files and loads values from it
 *
 * @category EnvParser
 * @package  Core
 * @author   Periyandavar <periyandavar@gmail.com>
 * @license  http://license.com license
 * @link     http://url.com
 */
class EnvLoader extends ConfigLoader
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
        $contents = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($contents as $line) {
            if (strpos(trim($line), '#') !== false) {
                continue;
            }
            if (trim($line) === '') {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);

            $data[$key] = $value;
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
        return ['env'];
    }
}
