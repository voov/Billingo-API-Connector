<?php
/**
 * Copyright (c) 2016, VOOV LLC.
 * All rights reserved.
 * Written by Daniel Fekete
 */

require '../vendor/autoload.php';

use Billingo\API\Connector\HTTP\Request;

$billingo = new Request([
	'public_key' => '28729a55286ef14dc086224b8678261e',
	'private_key' => '483c09c3d7e3299db447e3ec48be5a626197c58d590280c59cdd020282a4a957112bb3d9e6e928f75232caa0eae8a531745a0ef2643d4eff3e7b7cb4fa463bab'
													 ]);

$clients = $billingo->get('clients/497490869');

var_dump($clients);