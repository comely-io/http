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

use Comely\Http\Exception\ControllerException;
use Comely\Http\Exception\RouterException;
use Comely\Http\Query\Payload;
use Comely\Http\Request;
use Comely\Http\Response\ControllerResponse;
use Comely\Http\Router;
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
    /** @var string|null */
    protected ?string $entryPoint = null;

    use NotCloneableTrait;
    use NotSerializableTrait;

    /**
     * AbstractController constructor.
     * @param Router $router
     * @param Request $req
     * @param AbstractController|null $prev
     * @param string|null $entryPoint
     * @throws ControllerException
     */
    public function __construct(Router $router, Request $req, ?AbstractController $prev = null, ?string $entryPoint = null)
    {
        $this->router = $router;
        $this->request = $req;
        $this->response = $prev?->response() ?? new ControllerResponse();

        if ($entryPoint) {
            $this->entryPoint = method_exists($this, $entryPoint) ? $entryPoint : null;
            if (!$this->entryPoint) {
                throw new ControllerException(
                    sprintf('Entrypoint method "%s" does not exist in controller class "%s"', $entryPoint, get_called_class())
                );
            }
        }

        // Callback method will determine and call entrypoint method, prepare response structures, etc...
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
     * @param string $controllerClass
     * @param string $entryPoint
     * @return AbstractController
     * @throws ControllerException
     */
    public function forwardToController(string $controllerClass, string $entryPoint): AbstractController
    {
        if (!class_exists($controllerClass)) {
            throw new ControllerException('Cannot forward request; Controller class does not exist');
        }

        try {
            $reflect = new \ReflectionClass($controllerClass);
            if (!$reflect->isSubclassOf('Comely\Http\Router\AbstractController')) {
                throw new ControllerException(
                    'Forwarded to controller class does not extend "Comely\Http\Router\AbstractController"'
                );
            }
        } catch (\ReflectionException) {
            throw new ControllerException('Could not get reflection instance for next controller class');
        }

        return new $controllerClass($this->router, $this->request, $this, $entryPoint);
    }

    /**
     * @param string $path
     * @param string|null $method
     * @param bool|null $bypassHttpAuth
     * @return AbstractController
     * @throws RouterException
     * @throws \Comely\Http\Exception\HttpRequestException
     */
    public function forward(string $path, ?string $method = null, ?bool $bypassHttpAuth = true): AbstractController
    {
        // Create new Request
        $req = new Request($method ?? $this->request->method(), $path);
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
