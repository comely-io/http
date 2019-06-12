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

use Comely\Http\Request;
use Comely\Http\Response;
use Comely\Http\Router;

/**
 * Class AbstractController
 * @package Comely\Http\Router
 */
abstract class AbstractController
{
    /** @var Router */
    private $router;
    /** @var Request */
    private $request;
    /** @var Response */
    private $response;

    /**
     * AbstractController constructor.
     * @param Router $router
     * @param Request $req
     */
    public function __construct(Router $router, Request $req)
    {
        $this->router = $router;
        $this->request = $req;
        $this->response = new Response();

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
     * @return Response
     */
    public function response(): Response
    {
        return $this->response;
    }

    /**
     * @return Router
     */
    public function router(): Router
    {
        return $this->router;
    }
}