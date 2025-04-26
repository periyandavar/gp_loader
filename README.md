# GP Loader

The GP Loader package provides a configuration and dependency loading framework for PHP. It is designed for managing configurations from various sources, dependency injection, and loading of application components.

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Getting Started](#getting-started)
- [Features](#features)
- [Classes](#classes)
  - [ConfigLoader](#configloader)
  - [ArrayLoader](#arrayloader)
  - [EnvLoader](#envloader)
  - [ValueLoader](#valueloader)
  - [Container](#container)
  - [Loader](#loader)
- [Usage](#usage)
  - [LoadingConfiguration](#loading-configuration)
  - [DependencyInjection](#dependency-injection)
  - [ServiceContainer](#service-container)
  - [AutoLoadingClasses](#autoloading-classes)
- [Contributing](#contributing)
- [License](#license)
- [Author](#author)
- [Contact](#contact)

---

## Requirements

- PHP 7.3.5 or higher
- composer

---

## Installation

You can install `gp_loader` using Composer. Run the following command in your terminal:

```
composer require gp/loader
```
---

## Getting Started

After installation, you can start using the package by including the autoloader:

```
require 'vendor/autoload.php';
```
---

## Features

- **Flexible Configuration Loading**:
  - Supports loading configurations from multiple sources, including `.env` files, PHP arrays, and direct values.
  - Easy merging, overriding, and retrieval of configuration data.

- **Dependency Injection Container**:
  - Manage services and instances with a built-in DI container.
  - Supports singleton services and dynamic resolution of dependencies.
  - Simplifies service management with the `loadFromConfig` method.

- **Autoloading Classes**:
  - Automatically load classes based on namespace and path configurations.
  - Supports dynamic resolution of controllers and other components.

- **Extensible Design**:
  - Abstract classes like `ConfigLoader` provide the foundation for implementing custom loaders.
  - Easily extend functionality to support additional data sources or custom behaviors.

- **Environment Variable Integration**:
  - Load environment variables directly from `.env` files.
  - Simplifies managing sensitive configuration data.

- **Lightweight and Modular**:
  - Designed to be lightweight and modular, making it easy to integrate into any PHP project.
  - Provides only essential functionality without unnecessary overhead.

- **Error Handling and Validation**:
  - Includes methods for safely retrieving, setting, and overriding configuration values.
  - Ensures proper resolution of constructor dependencies and method calls.

- **Dynamic Service Resolution**:
  - Resolve services dynamically via `Container::resolve` and `Container::resolveMethod`.
  - Allows for flexible service binding and retrieval.

- **Factory-Based Loader Instantiation**:
  - Use `ConfigLoader::getInstance` to dynamically instantiate loaders based on the required type (e.g., `ENV_LOADER`, `ARRAY_LOADER`).

These features make the GP Loader framework powerful, extensible, and easy to use for managing configurations and dependencies in PHP applications.

---


## Classes

### ConfigLoader

An abstract class that acts as the base for configuration loaders. It defines the common methods and structure for loading configurations from various sources.

#### Methods
- `load()`: Loads the configuration data using the `innerLoader` method and sets up the load handler.
- `get(string $key)`: Retrieves a configuration value by its key.
- `getAll()`: Returns all configuration data.
- `merge(array $data)`: Merges additional data into the existing configuration.
- `set(string $key, $value, bool $strict = false)`: Sets a specific configuration value.
- `override(array $data)`: Overrides the current configuration with new data.
- `setLoadHandler(callable $_loadHandler)`: Sets a custom handler to process the loaded data.
- `defaultHandler($data)`: Default handler to load configuration values into environment variables.
- `getInstance($driver, $config = [], $name = '')`: Factory method to create an instance of a specific loader.
- `getConfig(string $name)`: Retrieves a named configuration instance.

#### Constants
- `ENV_LOADER`: Represents the environment file loader.
- `ARRAY_LOADER`: Represents the array-based loader.
- `VALUE_LOADER`: Represents the value-based loader.

---

### ArrayLoader

A concrete implementation of `ConfigLoader` that loads configuration data from a PHP array file.

#### Methods
- `innerLoader()`: Reads configuration data from a specified array file.

---

### EnvLoader

A concrete implementation of `ConfigLoader` that loads configuration data from a `.env` file.

#### Methods
- `innerLoader()`: Reads and parses a `.env` file, extracting key-value pairs.

---

### ValueLoader

A concrete implementation of `ConfigLoader` that directly uses the provided configuration data.

#### Methods
- `innerLoader()`: Returns the configuration data provided during initialization.

---

### Container

A dependency injection container for managing instances and services.

#### Methods
- `set(string $_name, mixed $_closure, bool $_singleton = false)`: Registers an instance or service.
- `getInstance(string $_name)`: Retrieves a singleton instance.
- `getService(string $_name)`: Retrieves a service.
- `get(string $_name)`: Retrieves an object from the container.
- `isClassRegistered(string $_name)`: Checks if a class is registered.
- `resolve(string $_class_name, array $data = [])`: Resolves and creates an instance of a class.
- `getConstrParams(string $_class_name, $data)`: Resolves constructor dependencies for a class.
- `loadFromConfig(array $_config)`: Loads configuration and registers services.
- `resolveMethod(string $class, string $method, array $data)`: Resolves dependencies for a specific method.

---

### Loader

A class responsible for managing the loading of application components.

#### Methods
- `autoLoadClass($ctrl, $autoloads, ?ConfigLoader $config = null)`: Automatically loads classes based on the configuration.
- `setPrefixes(array $prefixes)`: Sets the prefixes for autoloading classes.

---

## Usage

### Loading Configuration

You can load configurations using the `ConfigLoader` factory method:

```

use Loader\Config\ConfigLoader;

$config = ConfigLoader::getInstance(ConfigLoader::ENV_LOADER, ['file' => '.env']); $config->load();
echo $config->get('APP_ENV');

```

### Dependency Injection

Use the `Container` class to manage services and resolve dependencies:

```
use Loader\Container;

Container::set('db', function() {
    return new DatabaseConnection();
}, true);

$db = Container::get('db');

```

### Service Container

```
// Example configuration array for services
$config = [
    'services' => [
        'db' => [
            'class' => DatabaseConnection::class,
            'params' => [
                'host' => 'localhost',
                'username' => 'root',
                'password' => '',
                'database' => 'example_db',
            ],
            'singleton' => true
        ],
        'cache' => [
            'class' => CacheService::class,
            'params' => [
                'cache_dir' => '/tmp'
            ]
        ]
    ]
];

// Load configuration into the container
Container::loadFromConfig($config);

// Usage example: Get the database connection instance
$db = Container::get('db');
$db->connect();

// Usage example: Get the cache service
$cache = Container::get('cache');
$cache->save('key', 'value');

```

### Autoloading Classes

The `Loader` class can manage the loading of components, and make its available to your class object

```
use Loader\Loader;

$service = new Service(); // your custom service class
$autoloads = [
    'cache' => Cache::class,
    'db' => DB::class
];
$loader = Loader::autoLoadClass($service, $autoloads);

$service->db->getConnection(); // you can access the db class object directly in the service class object dynamically.
$service->cache->set('name', 'value'); // you can access the cache class object directly in the service class object dynamically.
```

---

### Contributing

Contributions are welcome! If you would like to contribute to gp_validator, please follow these steps:

- Fork the repository.
- Create a new branch (git checkout -b feature/- YourFeature).
- Make your changes and commit them (git commit -m 'Add some feature').
- Push to the branch (git push origin feature/YourFeature).
- Open a pull request.
- Please ensure that your code adheres to the coding standards and includes appropriate tests.

---

## License

This package is licensed under the MIT License. See the [LICENSE](https://github.com/periyandavar/gp_loader/blob/main/LICENSE) file for more information.

---

## Contact
For questions or issues, please reach out to the development team or open a ticket.

---


## Author

- Periyandavar [Github](https://github.com/periyandavar) (<vickyperiyandavar@gmail.com>)

---
