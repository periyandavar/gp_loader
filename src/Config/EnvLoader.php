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

use Loader\Exception\LoaderException;

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
            if (trim($line) === '') {
                continue;
            }
            list($key, $value) = explode('=', $line, 2);

            // Create the array
            $data[$key] = $value;
        }

        return $data;
    }
}
