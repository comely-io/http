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

use Comely\Http\Exception\RouteException;
use Comely\Http\Router;
use Comely\Utils\OOP\OOP;
use Comely\Utils\OOP\Traits\NotCloneableTrait;
use Comely\Utils\OOP\Traits\NotSerializableTrait;

/**
 * Class Route
 * @package Comely\Http\Router
 */
class Route
{
    /** @var int */
    public readonly int $id;
    /** @var string */
    public readonly string $path;
    /** @var string */
    public readonly string $matchPattern;
    /** @var string */
    public readonly string $controller;
    /** @var bool */
    private readonly bool $isNamespace;
    /** @var array */
    private array $ignorePathIndexes = [];
    /** @var null|string */
    private ?string $fallbackController = null;
    /** @var null|Router\Authentication\AbstractAuth */
    private ?Router\Authentication\AbstractAuth $auth = null;

    use NotCloneableTrait;
    use NotSerializableTrait;

    /**
     * @param Router $router
     * @param string $path
     * @param string $namespaceOrClass
     * @throws RouteException
     */
    public function __construct(private readonly Router $router, string $path, string $namespaceOrClass)
    {
        $this->id = $this->router->routesCount() + 1;

        // URL Path
        $path = "/" . trim(strtolower($path), "/"); // Case-insensitivity
        if (!preg_match('/^((\/?[\w\-.]+)|(\/\*))*(\/\*)?$/', $path)) {
            throw new RouteException('Route URL path argument contain an illegal character', $this->id);
        }

        // Controller or Namespace
        if (!preg_match('/^\w+(\\\\\w+)*(\\\\\*)?$/i', $namespaceOrClass)) {
            throw new RouteException('Class or namespace contains an illegal character', $this->id);
        }

        $urlIsWildcard = str_ends_with($path, '/*');
        $controllerIsWildcard = str_ends_with($namespaceOrClass, '\*');
        if ($controllerIsWildcard && !$urlIsWildcard) {
            throw new RouteException('Route URL must end with "/*"', $this->id);
        }

        $this->path = $path;
        $this->matchPattern = $this->pattern();
        $this->controller = $namespaceOrClass;
        $this->isNamespace = $controllerIsWildcard;
        $this->ignorePathIndexes = [];
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        return [
            "id" => $this->id,
            "path" => $this->path,
            "matchPattern" => $this->matchPattern,
            "controller" => $this->controller
        ];
    }

    /**
     * @param string $controller
     * @return Route
     * @throws RouteException
     */
    public function fallbackController(string $controller): self
    {
        if (!class_exists($controller)) {
            throw new RouteException('Fallback controller class is invalid or does not exist', $this->id);
        }

        $this->fallbackController = $controller;
        return $this;
    }

    /**
     * @return string
     */
    private function pattern(): string
    {
        // Init pattern from URL prop
        $pattern = "/^" . preg_quote($this->path, "/");

        // Last wildcard
        if (str_ends_with($pattern, "\/\*")) {
            $pattern = substr($pattern, 0, -4) . '(\/[\w\-\.]+)*';
        }

        // Optional trailing "/"
        $pattern .= "\/?";

        // Middle wildcards
        $pattern = str_replace('\*', '[^\/]?[\w\-\.]+', $pattern);

        // Finalise and return
        return $pattern . "$/";
    }

    /**
     * @param int ...$indexes
     * @return Route
     */
    public function ignorePathIndexes(int ...$indexes): self
    {
        $this->ignorePathIndexes = $indexes;
        return $this;
    }

    /**
     * @param Authentication\AbstractAuth $auth
     * @return Route
     */
    public function auth(Router\Authentication\AbstractAuth $auth): self
    {
        $this->auth = $auth;
        return $this;
    }

    /**
     * @param Request $req
     * @param bool $bypassHttpAuth
     * @return string|null
     */
    public function request(Request $req, bool $bypassHttpAuth = false): ?string
    {
        $path = $req->url->path;

        // RegEx match URL pattern
        if (!is_string($path) || !preg_match($this->matchPattern, $path)) {
            return null;
        }

        // Route Authentication
        if ($this->auth && !$bypassHttpAuth) {
            $this->auth->authenticate(
                $req->headers->get("authorization") // HTTP header "Authorization"
            );
        }

        // Find HTTP Controller
        $controllerClass = $this->controller;
        if ($this->isNamespace) {
            $pathIndex = -1;
            $controllerClass = array_map(function ($part) use (&$pathIndex) {
                $pathIndex++;
                if ($part && !in_array($pathIndex, $this->ignorePathIndexes)) {
                    return OOP::PascalCase($part);
                }

                return null;
            }, explode("/", trim($path, "/")));

            $namespace = substr($this->controller, 0, -2);
            $controllerClass = sprintf('%s\%s', $namespace, implode('\\', $controllerClass));
            $controllerClass = preg_replace('/\\\{2,}/', '\\', $controllerClass);
            $controllerClass = rtrim($controllerClass, '\\');
        }

        return $controllerClass && class_exists($controllerClass) ? $controllerClass : $this->fallbackController;
    }
}
