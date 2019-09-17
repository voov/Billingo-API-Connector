<?php

use Billingo\API\Connector\Exceptions\JSONParseException as JSONParseExceptionAlias;
use Billingo\API\Connector\Exceptions\RequestErrorException as RequestErrorExceptionAlias;
use Billingo\API\Connector\HTTP\Request as RequestAlias;
use GuzzleHttp\Exception\GuzzleException;

define("API_PUBLIC_KEY", "");
define("API_PRIVATE_KEY", "");

require_once "../vendor/autoload.php";

$request = new RequestAlias([
    'private_key' => API_PRIVATE_KEY,
    'public_key' => API_PUBLIC_KEY,
]);

try {

    // Get list of clients
    $clients = $request->get("clients");
    var_dump($clients);

} catch (JSONParseExceptionAlias $e) {
    echo "Error parsing response";
} catch (RequestErrorExceptionAlias $e) {
    echo "Error in request";
} catch (GuzzleException $e) {
    var_dump($e->getMessage());
}