<?php /** @noinspection PhpUnusedPrivateFieldInspection */
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
 * @property-read string $username
 * @property-read string $password
 */
class AuthUser
{
    /** @var string */
    private string $username;
    /** @var string */
    private string $password;

    /**
     * AuthUser constructor.
     * @param string $username
     * @param string $password
     */
    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @param $prop
     * @return string|null
     */
    public function __get($prop): ?string
    {
        return $this->$prop ?? null;
    }
}
