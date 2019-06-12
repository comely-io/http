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

use Comely\Http\Query\Headers;
use Comely\Http\Query\Payload;

/**
 * Class Response
 * @package Comely\Http
 */
class Response
{
    /** @var null|int */
    private $code;
    /** @var Headers */
    private $headers;
    /** @var Payload */
    private $payload;

    /**
     * Response constructor.
     */
    public function __construct()
    {
        $this->headers = new Headers();
        $this->payload = new Payload();
    }

    /**
     * If argument is passed, Sets new HTTP response code
     * Retrieves existing HTTP response code
     * @param int|null $new
     * @return int
     */
    public function code(?int $new = null): ?int
    {
        if ($new && $new > 0) {
            $this->code = $new;
        }

        return $this->code;
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
        return $this->payload;
    }
}