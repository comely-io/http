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
 * Class URL
 * @package Comely\Http\Common
 */
class URL
{
    /** @var string */
    public readonly string $complete;
    /** @var string|null */
    public readonly ?string $scheme;
    /** @var string|null */
    public readonly ?string $host;
    /** @var int|null */
    public readonly ?int $port;
    /** @var string|null */
    public readonly ?string $username;
    /** @var string|null */
    public readonly ?string $password;
    /** @var string|null */
    public readonly ?string $path;
    /** @var string|null */
    public readonly ?string $query;
    /** @var string|null */
    public readonly ?string $fragment;

    /**
     * @param string $url
     */
    public function __construct(string $url)
    {
        $parsed = parse_url($url);
        if (!is_array($parsed) || !$parsed) {
            throw new \InvalidArgumentException('Invalid URL');
        }

        $this->complete = $url;
        $this->scheme = $parsed["scheme"] ?? null;
        $this->host = $parsed["host"] ?? null;
        $this->port = $parsed["port"] ?? null;
        $this->username = $parsed["user"] ?? null;
        $this->password = $parsed["pass"] ?? null;
        $this->path = $parsed["path"] ?? null;
        $this->query = $parsed["query"] ?? null;
        $this->fragment = $parsed["fragment"] ?? null;
    }
}
