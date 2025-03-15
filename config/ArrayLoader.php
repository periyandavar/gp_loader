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

use Exception;

/**
 * EnvParser parse the env files and loads values from it
 *
 * @category EnvParser
 * @package  Core
 * @author   Periyandavar <periyandavar@gmail.com>
 * @license  http://license.com license
 * @link     http://url.com
 */
class ArrayLoader extends ConfigLoader
{

    /**
     * Loads env file values from .env file and add to $_ENV
     *
     * @return void
     */
    public function innerLoader(): array
    {
        $file = $this->config['file'] ?? '';
        if (empty($file)) {
            throw new Exception('env file not configured');
        }
        if (! (file_exists($file))) {
            throw new Exception('env file not found : ' . $file);
        }

        return require $file;
    }
}
