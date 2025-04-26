<?php

namespace Loader\Config;

class ValueLoader extends ConfigLoader
{
    /**
     * Loads the values
     *
     * @return array
     */
    public function innerLoader(): array
    {
        return $this->settings;
    }

    /**
     * Return valid file types
     *
     * @return string[]
     */
    public function getValidFileTypes(): array
    {
        return [];
    }
}
