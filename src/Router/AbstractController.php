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
use Comely\Http\Query\Payload;
use Comely\Http\Request;
use Comely\Http\Response\ControllerResponse;
use Comely\Http\Router;
use Comely\Utils\OOP\OOP;
use Comely\Utils\OOP\Traits\NotCloneableTrait;
use Comely\Utils\OOP\Traits\NotSerializableTrait;

/**
 * Class AbstractController
 * @package Comely\Http\Router
 */
abstract class AbstractController
{
    /** @var Router */
    private Router $router;
    /** @var Request */
    private Request $request;
    /** @var ControllerResponse */
    private ControllerResponse $response;

    use NotCloneableTrait;
    use NotSerializableTrait;

    /**
     * AbstractController constructor.
     * @param Router $router
     * @param Request $req
     */
    public function __construct(Router $router, Request $req)
    {
        $this->router = $router;
        $this->request = $req;
        $this->response = new ControllerResponse();

        $this->callback();
    }

    /**
     * @return void
     */
    abstract public function callback(): void;

    /**
     * @return Request
     */
    public function request(): Request
    {
        return $this->request;
    }

    /**
     * @return ControllerResponse
     */
    public function response(): ControllerResponse
    {
        return $this->response;
    }

    /**
     * @return Payload
     */
    public function input(): Payload
    {
        return $this->request->payload();
    }

    /**
     * @return Payload
     */
    public function output(): Payload
    {
        return $this->response->payload();
    }

    /**
     * @return Router
     */
    public function router(): Router
    {
        return $this->router;
    }

    /**
     * @return void
     */
    public function send(): void
    {
        $this->router->response()->send($this);
    }

    /**
     * @param string $pathOrController
     * @param string|null $method
     * @param bool|null $bypassHttpAuth
     * @return AbstractController
     * @throws RouterException
     * @throws \Comely\Http\Exception\HttpRequestException
     * @throws \ReflectionException
     */
    public function forward(string $pathOrController, ?string $method = null, ?bool $bypassHttpAuth = true): AbstractController
    {
        // Forward Request directly to a Controller
        if (OOP::isValidClassName($pathOrController)) {
            if ($method) {
                throw new RouterException(
                    'Second argument not accepted if forwarding directly to a controller class'
                );
            }

            if (!class_exists($pathOrController)) {
                throw new RouterException(
                    sprintf('Cannot forward request to "%s", class does not exist', $pathOrController)
                );
            }

            $reflect = new \ReflectionClass($pathOrController);
            if (!$reflect->isSubclassOf('Comely\Http\Router\AbstractController')) {
                throw new RouterException(
                    'Forwarded to controller class does not extend "Comely\Http\Router\AbstractController"'
                );
            }

            return $pathOrController($this->router, $this->request);
        }

        // Create new Request
        $req = new Request($method ?? $this->request->method(), $pathOrController);
        $req->override(
            clone $this->request->headers(),
            clone $this->request->payload(),
            clone $this->request->body()
        );

        return $this->router->request($req, $bypassHttpAuth);
    }

    /**
     * @param string $url
     * @param int|null $code
     */
    public function redirect(string $url, ?int $code = null): void
    {
        $code = $code ?? $this->response->getHttpCode();
        if ($code > 0) {
            http_response_code($code);
        }

        header(sprintf('Location: %s', $url));
        exit;
    }
}
