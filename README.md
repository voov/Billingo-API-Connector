# Billingo API Connector

This package is the PHP connector for the Billingo API 2.0.
The full API documentation is available [here](http://billingo.readthedocs.org/en/latest/).

## Installing

The easiest way to install the Connector is using Composer:

```
composer require voov/billingo-api-connector
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

#### JWT Time leeway

To adjust for some time skew between the client and the API server, you can set the `leeway` parameter when creating the new instance. The leeway is measured in seconds and the default value is 60. This modifies the `nbf`, `iat` and `exp` claims of the JWT, so in the case of the default leeway, the token is valid one minute before and after the issue time.

## General usage

### Get resource

```php
<?php
// Return the first page of the clients
$clients = $billingo->get('clients');
// Return the next page
$clients = $billingo->get('clients', ['page' => 2]);

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

### Download invoice

You can download the generated invoice in a PDF format

When passing the second parameter, you can specify a filename or a resource opened with `fopen` where the PDF will be saved. Otherwise a `GuzzleHttp\Psr7\Stream` is returned which you can read from.

```php
<?php
  $billingo->downloadInvoice('123456789', 'filename.pdf');
```

#### Using the stream interface

```php
<?php
  $invoice = $billingo->downloadInvoice('123456789');
  if($invoice->isReadable()) {
      while(!$invoice->eof()) {
          echo $invoice->read(1);
      }    
  }
```

