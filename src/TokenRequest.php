<?php
/**
 * Copyright (c) 2019, Daniel Fekete
 * All rights reserved.
 */

namespace Billingo\API\Connector;

use Billingo\API\Connector\Exceptions\SignatureInvalid;
use Billingo\API\Connector\Exceptions\TimingInvalid;

class TokenRequest
{
    // one minute delta
    const MAX_TIMING_DELTA = 60;

    /**
     * @var string
     */
    private $pubKey;
    /**
     * @var string
     */
    private $privateKey;

    public function __construct(string $pubKey, string $privateKey)
    {
        $this->pubKey = $pubKey;
        $this->privateKey = $privateKey;
    }

    /**
     * Generate token request data.
     *
     * @param $timing
     *
     * @return string
     */
    public function generate($timing): string
    {
        return implode('|', [$this->pubKey, $timing]);
    }

    /**
     * Return timing information (ie. unix epoch).
     *
     * @return int
     */
    public function generateTiming()
    {
        return time();
    }

    /**
     * Generate data string with signature.
     *
     * @param $timing
     *
     * @return string
     */
    public function generateWithSignature($timing)
    {
        $data = $this->generate($timing);

        return $data.'|'.$this->sign($data);
    }

    /**
     * Generate a data string with signature and timing
     *
     * @return string
     */
    public function generateWithSignatureAndTiming()
    {
        return $this->generateWithSignature($this->generateTiming());
    }

    /**
     * Return TRUE if timing is valid.
     *
     * @param $userTiming
     *
     * @return bool
     */
    public function validateTiming($userTiming): bool
    {
        return abs($this->generateTiming() - $userTiming) <= static::MAX_TIMING_DELTA;
    }

    /**
     * Validate user string to be valid.
     *
     * @param $userString
     * @param $timing
     *
     * @return bool
     */
    public function validateSignature($userString, $timing): bool
    {
        $data = $this->generate($timing);

        return hash_equals($this->sign($data), $userString);
    }

    /**
     * Return the data from the token request string.
     *
     * @param string $requestString
     *
     * @return array
     */
    public static function requestStringData(string $requestString): array
    {
        list($pubKey, $timing, $signature) = explode('|', $requestString);

        return compact('pubKey', 'timing', 'signature');
    }

    /**
     * Validate a full token request string.
     *
     * @param string $requestString
     * @param string $privateKey
     *
     * @return bool
     *
     * @throws SignatureInvalid
     * @throws TimingInvalid
     */
    public static function validateRequestString(string $requestString, string $privateKey): bool
    {
        $data = static::requestStringData($requestString);
        $self = new static($data['pubKey'], $privateKey);
        if (!$self->validateTiming($data['timing'])) {
            throw new TimingInvalid();
        }
        if (!$self->validateSignature($data['signature'], $data['timing'])) {
            throw new SignatureInvalid();
        }

        return true;
    }

    /**
     * Generate hash signature.
     *
     * @param string $data
     *
     * @return string
     */
    public function sign(string $data): string
    {
        return hash_hmac('sha256', $data, $this->privateKey);
    }
}
