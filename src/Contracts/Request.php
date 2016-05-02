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
     * @param $uri
     * @param array $data
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function get($uri, $data = array());

    /**
     * POST
     * @param $uri
     * @param array $data
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function post($uri, $data = array());

    /**
     * PUT
     * @param $uri
     * @param array $data
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function put($uri, $data = array());

    /**
     * DELETE
     * @param $uri
     * @param array $data
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function delete($uri, $data = array());
}