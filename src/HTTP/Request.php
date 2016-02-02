<?php
/**
 * Copyright (c) 2015, VOOV LLC.
 * All rights reserved.
 * Written by Daniel Fekete
 */

namespace Billingo\API\Connector\HTTP;

use Billingo\API\Client\Exceptions\JSONParseException;
use Billingo\API\Client\Exceptions\RequestErrorException;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;

class Request implements \Billingo\API\Connector\Contracts\Request
{
	/**
	 * @var Client
	 */
	private $client;


    private $config;

	/**
	 * Request constructor.
	 * @param Client $client
	 */
	public function __construct(Client $client)
	{
        $this->config = container('config');
		$this->client = $client;
	}


	public function generateAuthHeader()
	{
		$time = time();
		$signatureData = [
				'sub' => $this->config['public_key'],
				'iat' => $time,
				'exp' => $time +60,
				'iss' => $_SERVER['REQUEST_URI'],
				'nbf' => $time,
				'jti' => md5($this->config['public_key'] . $time)
		];

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
	public function request($method, $uri, $data=[])
	{

        // get the key to use for the query
        if($method == strtoupper('GET') || $method == strtoupper('DELETE')) $queryKey = 'query';
        else $queryKey = 'form_data';

        // make signature
        $response = $this->client->request($method, $uri, [$queryKey => $data, 'headers' =>[
			'Authorization' => 'Beamer ' . $this->generateAuthHeader()
		]]);


		$jsonData = json_decode($response->getBody(), true);
		if($jsonData == null) throw new JSONParseException('Cannot decode: ' . $response->getBody());
		if($response->getStatusCode() != 200 || $jsonData['success'] == 0)
			throw new RequestErrorException('Error: ' . $jsonData['msg'], $response->getStatusCode());

		return $jsonData;
	}

	/**
	 * GET
	 * @param $uri
	 * @param array $data
	 * @return mixed|\Psr\Http\Message\ResponseInterface
	 */
	public function get($uri, $data=[])
	{
		return $this->request('GET', $uri, $data);
	}

	/**
	 * POST
	 * @param $uri
	 * @param array $data
	 * @return mixed|\Psr\Http\Message\ResponseInterface
	 */
	public function post($uri, $data=[])
	{
		return $this->request('POST', $uri, $data);
	}

	/**
	 * PUT
	 * @param $uri
	 * @param array $data
	 * @return mixed|\Psr\Http\Message\ResponseInterface
	 */
	public function put($uri, $data = [])
	{
		return $this->request('PUT', $uri, $data);
	}


	/**
	 * DELETE
	 * @param $uri
	 * @param array $data
	 * @return mixed|\Psr\Http\Message\ResponseInterface
	 */
	public function delete($uri, $data = [])
	{
		return $this->request('DELETE', $uri, $data);
	}
}