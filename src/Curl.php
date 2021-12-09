<?php
/*
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

use Comely\Http\Common\HttpMethod;
use Comely\Http\Common\URL;
use Comely\Http\Curl\CurlQuery;

/**
 * Class Curl
 * @package Comely\Http
 */
class Curl
{
    /** @var static|null */
    private static ?self $instance = null;

    /**
     * @return static
     */
    public static function getInstance(): self
    {
        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @param string $url
     * @return CurlQuery
     */
    public static function Get(string $url): CurlQuery
    {
        return new CurlQuery(HttpMethod::GET, new URL($url));
    }

    /**
     * @param string $url
     * @return CurlQuery
     */
    public static function Post(string $url): CurlQuery
    {
        return new CurlQuery(HttpMethod::POST, new URL($url));
    }

    /**
     * @param string $url
     * @return CurlQuery
     */
    public static function Put(string $url): CurlQuery
    {
        return new CurlQuery(HttpMethod::PUT, new URL($url));
    }

    /**
     * @param string $url
     * @return CurlQuery
     */
    public static function Delete(string $url): CurlQuery
    {
        return new CurlQuery(HttpMethod::DELETE, new URL($url));
    }
}
