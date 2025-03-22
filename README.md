# gp_loader
A PHP Loader library to autoload the classes and a container to support the dependency injections

# usage
`
$data = ConfigLoader::getInstance(ConfigLoader::ENV_LOADER, ['file' => '.env'])->load();
`

<?php

use loader\config\ConfigLoader;

require "vendor/autoload.php";

$data = ConfigLoader::getInstance(ConfigLoader::ENV_LOADER, ['file' => '.env'])->load();

