<?php

use loader\config\ConfigLoader;

require "vendor/autoload.php";
// $data = ConfigLoader::getInstance(ConfigLoader::ARRAY_LOADER, ['file' => 'test.php'])->load();
$data = ConfigLoader::getInstance(ConfigLoader::ENV_LOADER, ['file' => '.env'])->load();

var_export(getenv('name'));

