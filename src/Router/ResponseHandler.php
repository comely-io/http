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

namespace Comely\Http\Router;

use Comely\Http\Exception\RouterException;
use Comely\Http\Query\Payload;
use Comely\Http\Response;
use Comely\Http\Router;

/**
 * Class ResponseHandler
 * @package Comely\Http\Router
 */
class ResponseHandler
{
    /** @var Router */
    private $router;
    /** @var array */
    private $handlers;
    /** @var \Closure */
    private $default;

    /**
     * ResponseHandler constructor.
     * @param Router $router
     * @throws RouterException
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
        $this->default(function (Payload $payload) {
            if ($payload->has("body")) {
                return print $payload->get("body");
            }

            return print_r($payload->array());
        });

        // Default handlers
        $this->handle("application/json", function (Payload $payload) {
            return print json_encode($payload->array());
        });
    }

    /**
     * @param \Closure $handler
     * @return ResponseHandler
     */
    public function default(\Closure $handler): self
    {
        $this->default = $handler;
        return $this;
    }

    /**
     * @param string $contentType
     * @param \Closure $handler
     * @return ResponseHandler
     * @throws RouterException
     */
    public function handle(string $contentType, \Closure $handler): self
    {
        if (!preg_match('/^[\w]+\/[\w]+$/i', $contentType)) {
            throw new RouterException('Invalid content type argument');
        }

        $this->handlers[strtolower($contentType)] = $handler;
        return $this;
    }

    /**
     * @param Response $res
     */
    public function send(Response $res): void
    {
        // Set HTTP response Code
        if ($res->code()) {
            http_response_code($res->code());
        }

        // Headers
        if ($res->headers()->count()) {
            foreach ($res->headers() as $key => $val) {
                header(sprintf('%s: %s', $key, $val));
            }
        }

        // Body
        $contentHandler = $this->default;
        $contentType = $res->headers()->get("content-type");
        if ($contentType) {
            $contentType = strtolower(trim(explode(";", $contentType)[0]));
            $contentHandler = $this->handlers[$contentType] ?? $contentHandler;
        }

        call_user_func($contentHandler, $res->payload());
    }
}