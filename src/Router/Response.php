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

namespace Comely\Http\Router;

use Comely\Buffer\Buffer;
use Comely\Http\Common\WriteHeaders;
use Comely\Http\Common\WritePayload;

/**
 * Class Response
 * @package Comely\Http\Router
 */
class Response
{
    /** @var int */
    private int $statusCode = 200;

    /**
     * @param WriteHeaders $headers
     * @param WritePayload $payload
     * @param Buffer $body
     */
    public function __construct(
        public readonly WriteHeaders $headers = new WriteHeaders([]),
        public readonly WritePayload $payload = new WritePayload([]),
        public readonly Buffer $body = new Buffer(),
    )
    {
    }

    /**
     * @param string $key
     * @param string|int|float|bool|array|null $value
     * @return $this
     */
    public function set(string $key, string|int|float|bool|null|array $value): self
    {
        $this->payload->set($key, $value);
        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function header(string $key, string $value): self
    {
        $this->headers->set($key, $value);
        return $this;
    }

    /**
     * @param int $code
     * @return $this
     */
    public function setHttpCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * @return int
     */
    public function getHttpStatusCode(): int
    {
        return $this->statusCode;
    }
}
