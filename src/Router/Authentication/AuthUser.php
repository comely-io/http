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
 * Class AuthUser
 * @package Comely\Http\Router\Authentication
 */
class AuthUser
{
    /**
     * AuthUser constructor.
     * @param string $username
     * @param string $password
     */
    public function __construct(public readonly string $username, public readonly string $password)
    {
    }
}
