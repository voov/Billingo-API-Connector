<?php
/**
 * Copyright (c) 2015, VOOV LLC.
 * All rights reserved.
 * Written by Daniel Fekete
 */

namespace Billingo\API\Connector\HTTP;


use Billingo\Config;

class Route
{
	private $host;
    /**
     * @var string
     */
    private $uri;

	/**
	 * Route constructor.
	 * @param Config $config
	 * @param string $uri
	 * @param $host
	 */
	public function __construct(Config $config, $uri = '',$host=null)
	{
        if($host == null) $host = $config->host;
		$this->host = rtrim($host, '/');
        $this->uri = $uri;
    }

	/**
	 * Generate full path
	 * @param array $params
	 * @param bool $absolute
	 * @return string
	 */
	public function path($params=array(), $absolute=false)
	{
		$paramsString = implode('/', (array)$params);
		$path = rtrim($this->uri, '/') . '/' . $paramsString;
		if($absolute) return $path;
		return $this->host . '/' . $path;
	}
}