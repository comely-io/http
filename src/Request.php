<?php
/**
 * This file is a part of "comely-io/http" package.
 * https://github.com/comely-io/http
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comely-io/http/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\Http;

use Comely\Http\Exception\HttpRequestException;
use Comely\Http\Query\Authentication;
use Comely\Http\Query\Headers;
use Comely\Http\Query\Payload;
use Comely\Http\Query\SSL;
use Comely\Http\Query\URL;

/**
 * Class Request
 * @package Comely\Http
 */
class Request
{
    public const METHODS = ["GET", "POST", "PUT", "DELETE"];

    /** @var string */
    protected $method;
    /** @var string */
    protected $url;
    /** @var Headers */
    protected $headers;
    /** @var Payload */
    protected $body;
    /** @var null|Authentication */
    protected $auth;
    /** @var null|SSL */
    protected $ssl;

    /**
     * Request constructor.
     * @param string $method
     * @param string $url
     * @throws HttpRequestException
     */
    public function __construct(string $method, string $url)
    {
        // HTTP method
        $this->method = strtoupper($method);
        if (!in_array($method, self::METHODS)) {
            throw new HttpRequestException('Invalid HTTP request method');
        }

        $this->url = new URL($url);
        $this->headers = new Headers();
        $this->body = new Payload();
    }

    /**
     * @return string
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * @return URL
     */
    public function url(): URL
    {
        return $this->url;
    }

    /**
     * @return Headers
     */
    public function headers(): Headers
    {
        return $this->headers;
    }

    /**
     * @return Payload
     */
    public function payload(): Payload
    {
        return $this->body;
    }

    /**
     * @return Authentication
     */
    public function auth(): Authentication
    {
        if (!$this->auth) {
            $this->auth = new Authentication();
        }

        return $this->auth;
    }

    /**
     * @return SSL
     * @throws Exception\SSL_Exception
     */
    public function ssl(): SSL
    {
        if (!$this->ssl) {
            $this->ssl = new SSL();
        }

        return $this->ssl;
    }

    /**
     * @param mixed ...$props
     */
    public function override(...$props): void
    {
        foreach ($props as $prop) {
            if ($prop instanceof Headers) {
                $this->headers = $prop;
                return;
            }

            if ($prop instanceof Payload) {
                $this->body = $prop;
                return;
            }

            if ($prop instanceof URL) {
                $this->url = $prop;
                return;
            }
        }
    }
}