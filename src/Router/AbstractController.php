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

use Comely\Http\Common\ReadPayload;
use Comely\Http\Common\WritePayload;
use Comely\Http\Exception\ControllerException;
use Comely\Http\Router;
use Comely\Utils\OOP\Traits\NoDumpTrait;
use Comely\Utils\OOP\Traits\NotCloneableTrait;
use Comely\Utils\OOP\Traits\NotSerializableTrait;

/**
 * Class AbstractController
 * @package Comely\Http\Router
 */
abstract class AbstractController
{
    public readonly Response $response;

    use NoDumpTrait;
    use NotCloneableTrait;
    use NotSerializableTrait;

    /**
     * @param Router $router
     * @param Request $request
     * @param AbstractController|null $prev
     * @param string|null $entryPoint
     * @throws ControllerException
     */
    public function __construct(
        public              readonly Router $router,
        public              readonly Request $request,
        ?AbstractController $prev = null,
        protected ?string   $entryPoint = null)
    {
        $this->response = $prev?->response ?? new Response();

        if ($entryPoint) {
            $this->entryPoint = method_exists($this, $entryPoint) ? $entryPoint : null;
            if (!$this->entryPoint) {
                throw new ControllerException(
                    sprintf('Entrypoint method "%s" does not exist in controller class "%s"', $entryPoint, static::class)
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
     * @return ReadPayload
     */
    public function input(): ReadPayload
    {
        return $this->request->payload;
    }

    /**
     * @return WritePayload
     */
    public function output(): WritePayload
    {
        return $this->response->payload;
    }

    /**
     * @return void
     */
    public function send(): void
    {
        $this->router->response->send($this);
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
        } catch (\Exception) {
            throw new ControllerException('Could not get reflection instance for next controller class');
        }

        return new $controllerClass($this->router, $this->request, $this, $entryPoint);
    }

    /**
     * @param string $url
     * @param int|null $code
     */
    public function redirect(string $url, ?int $code = null): void
    {
        $code = $code ?? $this->response->getHttpStatusCode();
        if ($code > 0) {
            http_response_code($code);
        }

        header(sprintf('Location: %s', $url));
        exit;
    }
}
