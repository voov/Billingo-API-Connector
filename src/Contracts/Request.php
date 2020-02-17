<?php
/**
 * Copyright (c) 2016, VOOV LLC.
 * All rights reserved.
 * Written by Daniel Fekete
 */

namespace Billingo\API\Connector\Contracts;


interface Request
{

    /**
     * GET
     * @param string $uri
     * @param array $data
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function get($uri, $data = []);

    /**
     * POST
     * @param string $uri
     * @param array $data
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function post($uri, $data = []);

    /**
     * PUT
     * @param string $uri
     * @param array $data
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function put($uri, $data = []);

    /**
     * DELETE
     * @param string $uri
     * @param array $data
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function delete($uri, $data = []);
}