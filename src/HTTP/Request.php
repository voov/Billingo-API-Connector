<?php
/**
 * Copyright (c) 2015, VOOV LLC.
 * All rights reserved.
 * Written by Daniel Fekete
 */

namespace Billingo\API\Connector\HTTP;

use Billingo\API\Connector\Exceptions\JSONParseException;
use Billingo\API\Connector\Exceptions\RequestErrorException;
use Firebase\JWT\JWT;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Request implements \Billingo\API\Connector\Contracts\Request
{
	/**
	 * @var Client
	 */
	private $client;


    private $config;

	/**
	 * Request constructor.
	 * @param $options
	 */
	public function __construct($options)
	{
        $this->config = $this->resolveOptions($options);
	}

	/**
	 * Get required options for the Billingo API to work
	 * @param $opts
	 * @return mixed
	 */
	protected function resolveOptions($opts)
	{
		$resolver = new OptionsResolver();
		$resolver->setDefault('version', '2');
		$resolver->setDefault('host', 'https://www.billingo.hu/api/'); // might be overridden in the future
		$resolver->setRequired(array('host', 'private_key', 'public_key', 'version'));
		return $resolver->resolve($opts);
	}

	/**
	 * Generate URL
	 * @param $uri
	 * @param array $data
	 * @return string
	 */
	public function getURL($uri, $data = array())
	{
		$host = rtrim($this->config['host'], '/');
		$uri = trim($uri, '/');

		$url = "{$host}/{$uri}";

		if(count($data) > 0) $url .= '?' . http_build_query($data);

		return $url;
	}

	/**
	 * Generate JWT authorization header
	 * @return string
	 */
	public function generateAuthHeader()
	{
		$time = time();
		$iss = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'cli';
		$signatureData = array(
				'sub' => $this->config['public_key'],
				'iat' => $time,
				'exp' => $time +60,
				'iss' => $iss,
				'nbf' => $time,
				'jti' => md5($this->config['public_key'] . $time)
		);

		return JWT::encode($signatureData, $this->config['private_key']);
	}

	/**
	 * Make a request to the Billingo API
	 * @param $method
	 * @param $uri
	 * @param array $data
	 * @return mixed|array
	 * @throws JSONParseException
	 * @throws RequestErrorException
	 */
	public function request($method, $uri, $data=array())
	{
		$c = curl_init();
		$headers = array('Authorization: Bearer ' . $this->generateAuthHeader());

		$method = strtoupper($method);

		curl_setopt($c, CURLOPT_CUSTOMREQUEST, $method);

		// get the key to use for the query
		if($method == 'GET' || $method == 'DELETE') {

			$url = $this->getURL($uri, $data);
		}
		else {
			$jsonString = json_encode($data);
			$jsonStringLen = strlen($jsonString);

			$headers[] = 'Content-type: application/json';
			$headers[] = "Content-length: {$jsonStringLen}";

			$url = $this->getURL($uri);

			curl_setopt($c, CURLOPT_POSTFIELDS, $jsonString);
		}


		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($c);
		$statusCode = curl_getinfo($c, CURLINFO_HTTP_CODE);
		curl_close($c);

		$jsonData = json_decode($response, true);
		if($jsonData == null) throw new JSONParseException('Cannot decode: ' . $response);
		if($statusCode != 200 || $jsonData['success'] == 0)
			throw new RequestErrorException('Error: ' . $jsonData['error'], $statusCode);


		return $jsonData['data'];
	}

	/**
	 * GET
	 * @param $uri
	 * @param array $data
	 * @return mixed
	 */
	public function get($uri, $data=array())
	{
		return $this->request('GET', $uri, $data);
	}

	/**
	 * POST
	 * @param $uri
	 * @param array $data
	 * @return mixed
	 */
	public function post($uri, $data=array())
	{
		return $this->request('POST', $uri, $data);
	}

	/**
	 * PUT
	 * @param $uri
	 * @param array $data
	 * @return mixed
	 */
	public function put($uri, $data = array())
	{
		return $this->request('PUT', $uri, $data);
	}


	/**
	 * DELETE
	 * @param $uri
	 * @param array $data
	 * @return mixed
	 */
	public function delete($uri, $data = array())
	{
		return $this->request('DELETE', $uri, $data);
	}
}