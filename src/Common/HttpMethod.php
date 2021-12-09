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

namespace Comely\Http\Common;

/**
 * Class HttpMethod
 * @package Comely\Http\Common
 */
enum HttpMethod
{
    case GET;
    case POST;
    case PUT;
    case DELETE;
    case OPTIONS;

    /**
     * @param string $method
     * @return static|null
     */
    public static function fromString(string $method): ?self
    {
        return match (strtolower($method)) {
            "get" => self::GET,
            "post" => self::POST,
            "put" => self::PUT,
            "delete" => self::DELETE,
            "options" => self::OPTIONS,
            default => null,
        };
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return match ($this) {
            self::GET => "GET",
            self::POST => "POST",
            self::PUT => "PUT",
            self::DELETE => "DELETE",
            self::OPTIONS => "OPTIONS",
        };
    }
}
