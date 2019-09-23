<?php
/**
 * Copyright (c) 2015, VOOV LLC.
 * All rights reserved.
 * Written by Daniel Fekete.
 */

namespace Billingo\API\Connector\HTTP;

use Billingo\API\Connector\Exceptions\JSONParseException;
use Billingo\API\Connector\Exceptions\RequestErrorException;
use Billingo\API\Connector\TokenRequest;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Request implements \Billingo\API\Connector\Contracts\Request
{
    /**
     * @var Client
     */
    private $client;

    /** @var array */
    private $config;

    /**
     * @var OptionsResolver
     */
    private $resolver;

    /**
     * Request constructor.
     *
     * @param $options
     */
    public function __construct($options)
    {
        $this->config = $this->resolveOptions($options);
        $this->client = new Client([
                'verify' => false,
                'base_uri' => $this->config['host'],
                'debug' => false,
        ]);
    }

    /**
     * Get required options for the Billingo API to work.
     *
     * @param $opts
     *
     * @return mixed
     */
    protected function resolveOptions($opts)
    {
        $this->resolver = new OptionsResolver();
        $this->resolver->setDefault('version', '2');
        $this->resolver->setDefault('host', 'https://www.billingo.hu/api/'); // might be overridden in the future
        $this->resolver->setDefault('leeway', 60);
        $this->resolver->setDefault('headers', []);
        $this->resolver->setRequired(['host', 'version', 'leeway']);

        if (array_key_exists('token', $opts)) {
            $this->resolver->setRequired('token');
        } else {
            $this->resolver->setRequired(['private_key', 'public_key']);
        }

        return $this->resolver->resolve($opts);
    }

    /**
     * Make a request to the Billingo API.
     *
     * @param $method
     * @param $uri
     * @param array $data
     *
     * @return mixed|array
     *
     * @throws JSONParseException
     * @throws RequestErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($method, $uri, $data = [])
    {
        // get the key to use for the query
        if ($method == strtoupper('GET') || $method == strtoupper('DELETE')) {
            $queryKey = 'query';
        } else {
            $queryKey = 'json';
        }

        $headers = array_merge_recursive($this->config['headers'], $this->generateAuthHeader());
        $response = $this->client->request($method, $uri, [$queryKey => $data, 'headers' => $headers]);

        $jsonData = json_decode($response->getBody(), true);

        if (null == $jsonData) {
            throw new JSONParseException('Cannot decode: '.$response->getBody());
        }

        if (200 != $response->getStatusCode() || 0 == $jsonData['success']) {
            throw new RequestErrorException('Error: '.$jsonData['error'], $response->getStatusCode());
        }

        if (array_key_exists('data', $jsonData)) {
            return $jsonData['data'];
        }

        return [];
    }

    /**
     * GET.
     *
     * @param $uri
     * @param array $data
     *
     * @return mixed|ResponseInterface
     *
     * @throws JSONParseException
     * @throws RequestErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get($uri, $data = [])
    {
        return $this->request('GET', $uri, $data);
    }

    /**
     * POST.
     *
     * @param $uri
     * @param array $data
     *
     * @return mixed|ResponseInterface
     *
     * @throws JSONParseException
     * @throws RequestErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post($uri, $data = [])
    {
        return $this->request('POST', $uri, $data);
    }

    /**
     * PUT.
     *
     * @param $uri
     * @param array $data
     *
     * @return mixed|ResponseInterface
     *
     * @throws JSONParseException
     * @throws RequestErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function put($uri, $data = [])
    {
        return $this->request('PUT', $uri, $data);
    }

    /**
     * DELETE.
     *
     * @param $uri
     * @param array $data
     *
     * @return mixed|ResponseInterface
     *
     * @throws JSONParseException
     * @throws RequestErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete($uri, $data = [])
    {
        return $this->request('DELETE', $uri, $data);
    }

    /**
     * Downloads the given invoice.
     *
     * @param $id
     * @param resource|string|null $file
     *
     * @return \Psr\Http\Message\StreamInterface|string|null
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function downloadInvoice($id, $file = null)
    {
        $uri = "invoices/{$id}/download";
        $options = ['headers' => $this->generateAuthHeader()];
        if (!is_null($file)) {
            $options['sink'] = $file;
        }
        $response = $this->client->request('GET', $uri, $options);

        return $response instanceof ResponseInterface ? $response->getBody() : null;
    }

    /**
     * Get billingo token for user.
     *
     * @param $pubKey
     * @param $privateKey
     *
     * @return string Billingo token
     *
     * @throws JSONParseException
     * @throws RequestErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getBillingoToken($pubKey, $privateKey)
    {
        $tr = new TokenRequest($pubKey, $privateKey);
        $response = $this->get('token', ['tokenrequest' => $tr->generateWithSignatureAndTiming()]);

        return $response['token'];
    }

    /**
     * Generate JWT authorization header.
     *
     * @return string
     */
    public function generateJWTArray()
    {
        $time = time();
        $iss = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'cli';
        $signatureData = [
            'sub' => $this->config['public_key'],
            'iat' => $time - $this->config['leeway'],
            'exp' => $time + $this->config['leeway'],
            'iss' => $iss,
            'nbf' => $time - $this->config['leeway'],
            'jti' => md5($this->config['public_key'].$time),
        ];

        return JWT::encode($signatureData, $this->config['private_key']);
    }

    /**
     * Generate authentication header based on JWT.
     *
     * @return array
     */
    protected function generateJWTHeader()
    {
        return [
            'Authorization' => 'Bearer '.$this->generateJWTArray(),
        ];
    }

    /**
     * When using BillingoToken for authentication
     * use this function to generate the correct header.
     *
     * @return array
     */
    protected function generateBillingoTokenHeader()
    {
        return [
            'X-Billingo-Token' => $this->config['token'],
        ];
    }

    /**
     * Generate the correct authentication header(s)
     * either JWT or BillingoToken.
     *
     * @return array
     */
    protected function generateAuthHeader()
    {
        if ($this->resolver->isDefined('token')) {
            return $this->generateBillingoTokenHeader();
        } else {
            return $this->generateJWTHeader();
        }
    }
}
