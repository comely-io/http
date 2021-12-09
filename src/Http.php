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

use Comely\Http\Router\Request;

/**
 * Class Http
 * @package Comely\Http
 */
class Http
{
    /** string Version (Major.Minor.Release-Suffix) */
    public const VERSION = "2.1.0";
    /** int Version (Major * 10000 + Minor * 100 + Release) */
    public const VERSION_ID = 20100;

    // HTTP version
    public const HTTP_VERSION_1 = CURL_HTTP_VERSION_1_0;
    public const HTTP_VERSION_1_1 = CURL_HTTP_VERSION_1_1;
    public const HTTP_VERSION_2 = CURL_HTTP_VERSION_2_0;

    public const HTTP_VERSIONS = [
        self::HTTP_VERSION_1,
        self::HTTP_VERSION_1_1,
        self::HTTP_VERSION_2
    ];

    // Status Codes Messages
    public const STATUS = [
        100 => "Continue",
        200 => "OK",
        201 => "Created",
        202 => "Accepted",
        204 => "No Content",
        304 => "Not Modified",
        400 => "Bad Request",
        401 => "Unauthorized",
        403 => "Forbidden",
        404 => "Not Found",
        409 => "Conflict",
        500 => "Internal Server Error"
    ];
}
