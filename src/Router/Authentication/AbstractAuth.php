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

namespace Comely\Http\Router\Authentication;

/**
 * Class AbstractAuth
 * @package Comely\Http\Router\Authentication
 */
abstract class AbstractAuth
{
    /** @var array */
    protected array $users = [];
    /** @var null|callable */
    protected $unauthorized = null;

    /**
     * @param string $realm
     */
    public function __construct(protected readonly string $realm)
    {
    }

    /**
     * @param string $username
     * @param string $password
     * @return $this
     */
    final public function user(string $username, string $password): static
    {
        $this->users[$username] = new AuthUser($username, $password);
        return $this;
    }

    /**
     * @param callable $callback
     * @return $this
     */
    final public function unauthorized(callable $callback): static
    {
        $this->unauthorized = $callback;
        return $this;
    }

    /**
     * @param string|null $authorization
     */
    abstract public function authenticate(?string $authorization): void;
}
