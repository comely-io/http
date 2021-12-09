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

namespace Comely\Http;

use Comely\Http\Exception\RouterException;
use Comely\Http\Router\AbstractController;
use Comely\Http\Router\Request;
use Comely\Http\Router\ResponseHandler;
use Comely\Http\Router\Route;
use Comely\Utils\OOP\Traits\NoDumpTrait;
use Comely\Utils\OOP\Traits\NotCloneableTrait;
use Comely\Utils\OOP\Traits\NotSerializableTrait;

/**
 * Class Router
 * @package Comely\Http
 */
class Router
{
    /** @var array */
    private array $routes = [];
    /** @var int */
    private int $count = 0;
    /** @var null|string */
    private ?string $fallbackController = null;
    /** @var ResponseHandler */
    public readonly ResponseHandler $response;

    use NotCloneableTrait;
    use NotSerializableTrait;
    use NoDumpTrait;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $this->response = new ResponseHandler();
    }

    /**
     * @return int
     */
    public function routesCount(): int
    {
        return $this->count;
    }

    /**
     * @param string $controller
     * @return $this
     * @throws RouterException
     */
    public function fallbackController(string $controller): self
    {
        if (!class_exists($controller)) {
            throw new RouterException('Default router fallback controller class is invalid or does not exist');
        }

        $this->fallbackController = $controller;
        return $this;
    }

    /**
     * @param string $path
     * @param string $controllerClassOrNamespace
     * @return Route
     * @throws Exception\RouteException
     */
    public function route(string $path, string $controllerClassOrNamespace): Route
    {
        $route = new Route($this, $path, $controllerClassOrNamespace);
        $this->routes[] = $route;
        $this->count++;
        return $route;
    }

    /**
     * @param Request $req
     * @param bool $bypassHttpAuth
     * @return AbstractController
     * @throws RouterException
     */
    public function request(Request $req, bool $bypassHttpAuth = false): AbstractController
    {
        // Find controller
        $controller = null;
        /** @var Route $route */
        foreach ($this->routes as $route) {
            $controller = $route->request($req, $bypassHttpAuth);
            if ($controller) {
                break;
            }
        }

        $controller = $controller ?? $this->fallbackController;
        if (!$controller) {
            throw new RouterException('Could not route request to any controller');
        }

        try {
            $reflect = new \ReflectionClass($controller);
            if (!$reflect->isSubclassOf('Comely\Http\Router\AbstractController')) {
                throw new RouterException('Controller class does not extend "Comely\Http\Router\AbstractController"');
            }
        } catch (\ReflectionException) {
            throw new RouterException('Could not get reflection instance for controller class');
        }

        return new $controller($this, $req);
    }
}
