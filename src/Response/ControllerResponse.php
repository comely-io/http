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

namespace Comely\Http\Response;

/**
 * Class ControllerResponse
 * @package Comely\Http\Response
 */
class ControllerResponse extends AbstractResponse
{
    /**
     * ControllerResponse constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->code = 200; // Default 200
    }

    /**
     * @param string $key
     * @param $value
     * @return ControllerResponse
     */
    public function set(string $key, $value): self
    {
        $this->payload()->set($key, $value);
        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     * @return ControllerResponse
     */
    public function header(string $name, string $value): self
    {
        $this->headers()->set($name, $value);
        return $this;
    }

    /**
     * @param int $httpStatusCode
     * @return ControllerResponse
     */
    public function setHttpCode(int $httpStatusCode): self
    {
        $this->code = $httpStatusCode;
        return $this;
    }

    /**
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->code;
    }
}
