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

use Comely\Http\Exception\RouterException;
use Comely\Utils\OOP\Traits\NoDumpTrait;
use Comely\Utils\OOP\Traits\NotCloneableTrait;
use Comely\Utils\OOP\Traits\NotSerializableTrait;

/**
 * Class ResponseHandler
 * @package Comely\Http\Router
 */
class ResponseHandler
{
    /** @var array */
    private array $handlers;
    /** @var \Closure */
    private \Closure $default;

    use NotCloneableTrait;
    use NotSerializableTrait;
    use NoDumpTrait;

    /**
     * ResponseHandler constructor.
     * @throws RouterException
     */
    public function __construct()
    {
        $this->default(function (Response $res) {
            if ($res->body->len()) {
                return print $res->body->raw();
            }

            return print_r($res->payload->array());
        });

        // Default handlers
        $this->handle("application/json", function (Response $res) {
            return print json_encode($res->payload->array());
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
     * @param AbstractController $controller
     */
    public function send(AbstractController $controller): void
    {
        $res = $controller->response;

        // Set HTTP response Code
        if ($res->getHttpStatusCode()) {
            http_response_code($res->getHttpStatusCode());
        }

        // Is Explicit Content-Type specified?
        $contentType = $res->headers->get("content-type");
        if (!$contentType) {
            // Not specified, Try Request's ACCEPT header
            $accept = $controller->request->headers->get("accept");
            if (is_string($accept) && $accept) {
                $acceptTypes = trim(explode(";", $accept)[0]);
                $contentType = $this->findHandler(explode(",", $acceptTypes));
                if ($contentType) {
                    $res->headers->set("Content-Type", $contentType);
                }
            }
        }

        // Headers
        if ($res->headers->count()) {
            foreach ($res->headers as $key => $val) {
                header(sprintf('%s: %s', $key, $val));
            }
        }

        // Body
        $contentHandler = $this->default;
        if ($contentType) {
            $contentType = strtolower(trim(explode(";", $contentType)[0]));
            $contentHandler = $this->handlers[$contentType] ?? $contentHandler;
        }

        call_user_func($contentHandler, $res);
    }

    /**
     * @param array $types
     * @return string
     */
    private function findHandler(array $types): string
    {
        $first = array_shift($types);
        if (isset($this->handlers[strtolower($first)])) {
            return $first; // First accept opt has registered handler
        }

        // Check if any of other opts have a registered handler
        if ($types) {
            foreach ($types as $type) {
                if (isset($this->handlers[strtolower($type)])) {
                    return $type;
                }
            }
        }

        // No registered handler, just return first opt (for default handler)
        return $first;
    }
}
