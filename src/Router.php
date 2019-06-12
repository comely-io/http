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

use Comely\DataTypes\OOP;
use Comely\Http\Exception\RouterException;
use Comely\Http\Router\AbstractController;
use Comely\Http\Router\Route;

/**
 * Class Router
 * @package Comely\Http
 */
class Router
{
    /** @var array */
    private $routes;
    /** @var int */
    private $count;
    /** @var null|string */
    private $fallbackController;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $this->routes = [];
        $this->count = 0;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * @param string $controller
     * @return Router
     * @throws RouterException
     */
    public function fallbackController(string $controller): self
    {
        if (!OOP::isValidClass($controller)) {
            throw new RouterException('Default router fallback controller class is invalid or does not exist');
        }

        $this->fallbackController = $controller;
        return $this;
    }

    /**
     * @param string $uri
     * @param string $controllerClassOrNamespace
     * @return Route
     * @throws Exception\RouteException
     */
    public function route(string $uri, string $controllerClassOrNamespace): Route
    {
        $route = new Route($this, $uri, $controllerClassOrNamespace);
        $this->routes[] = $route;
        $this->count++;
        return $route;
    }

    /**
     * @param Request $req
     * @return AbstractController
     * @throws RouterException
     * @throws \ReflectionException
     */
    public function request(Request $req): AbstractController
    {
        $controller = null;
        /** @var Route $route */
        foreach ($this->routes as $route) {
            $controller = $route->request($req);
            if ($controller) {
                break;
            }
        }

        $controller = $controller ?? $this->fallbackController;
        if (!$controller) {
            throw new RouterException(
                'Could not route request to any controller; No default/fallback controller is defined'
            );
        }

        $reflect = new \ReflectionClass($controller);
        if (!$reflect->isSubclassOf('Comely\Http\Router\AbstractController')) {
            throw new RouterException('Controller class does not extend "Comely\Http\Router\AbstractController"');
        }

        return new $controller($this, $req);
    }
}