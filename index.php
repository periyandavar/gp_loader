<?php

use Loader\Config\ConfigLoader;

require "vendor/autoload.php";
ConfigLoader::getInstance(ConfigLoader::ENV_LOADER, ['file' => '.env'])->load();
