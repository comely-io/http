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

use Comely\Http\Query\AbstractReqRes;
use Comely\Http\Query\Headers;
use Comely\Http\Query\Payload;
use Comely\Http\Query\ResponseBody;

/**
 * Class Response
 * @package Comely\Http
 */
class Response extends AbstractReqRes
{
    /** @var null|int */
    private $code;
    /** @var null|ResponseBody */
    private $body;

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
     * @return ResponseBody|null
     */
    public function body(): ?ResponseBody
    {
        return $this->body;
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
                $this->payload = $prop;
                return;
            }

            if ($prop instanceof ResponseBody) {
                $this->body = $prop;
                return;
            }
        }
    }
}