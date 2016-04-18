# Billingo API Connector

This package is the PHP connector for the Billingo API 2.0.
The full API documentation is available [here](http://billingo.readthedocs.org/en/latest/).  
**Important:** This version uses cURL instead of Guzzle for HTTP requests, this way it is compatible with older
 PHP versions. However the usage of this version is not recommended if newer PHP (>5.6.0) is available!

## Installing

The easiest way to install the Connector is using Composer:

```
composer require voov/billingo-api-connector:dev-cron
```

Then use your framework's autoload, or simply add:

```php
<?php
  require 'vendor/autoload.php';
```

## Manual install

If you with to omit using Composer altogether, you can download the sources from the repository and use any [PSR-4](http://www.php-fig.org/psr/psr-4/) compatible autoloader.

Registering your own autoloader is also a way (altough not recommended):

```php
<?php
// Source: http://www.php-fig.org/psr/psr-4/examples/
spl_autoload_register(function ($class) {

    // project-specific namespace prefix
    $prefix = 'Billingo\\API\\Connector';

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/src/';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
```

## Getting started

You can start making requests to the Billingo API just by creating a new `Request` instance

```php
<?php
  use Billingo\API\Connector\HTTP\Request;

  $billingo = new Request([
	  'public_key' => 'YOUR_PUBLIC_KEY',
	  'private_key' => 'YOUR_PRIVATE_KEY'
  ]);
```

The `Request` class takes care of the communication between your app and the Billingo API server with JWT authorization handled in the background.

## General usage

### Get resource

```php
<?php
// Return the list of clients
$clients = $billingo->get('clients');

// Return one client
$client = $billingo->get('clients/123456789');

```

### Save resource

```php
<?php
// save a new client
$clientData = [
  "name" => "Gigazoom LLC.",
  "email" => "rbrooks5@amazon.com",
  "billing_address" => [
      "street_name" => "Moulton",
      "street_type" => "Terrace",
      "house_nr" => "2797",
      "city" => "Preston",
      "postcode" => "PR1",
      "country" => "United Kingdom"
  ]
]
$billingo->post('clients', $clientData);

```

### Update resource

```php
<?php
// save client
$billingo->put('clients/123456789', $newData);
```

### Delete resource

```php
<?php
// delete client
$billingo->delete('clients/123456789');
```
