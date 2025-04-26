<?php

namespace Loader\Config;

use Symfony\Component\Yaml\Yaml;

class YamlLoader extends ConfigLoader
{
    /**
     * Loads yaml file values from .yml or .yaml file.
     *
     * @return array
     */
    public function innerLoader(): array
    {
        return Yaml::parseFile($this->getFile());
    }

    /**
     * Return valid file types
     *
     * @return string[]
     */
    public function getValidFileTypes(): array
    {
        return ['yaml', 'yml'];
    }
}
