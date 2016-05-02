<?php
/**
 * Copyright (c) 2016, VOOV LLC.
 * All rights reserved.
 * Written by Daniel Fekete
 */

require '../vendor/autoload.php';

use Billingo\API\Connector\HTTP\Request;

$billingo = new Request(array(
	'public_key' => '',
	'private_key' => ''
));

$clients = $billingo->get('clients/497490869');

var_dump($clients);
