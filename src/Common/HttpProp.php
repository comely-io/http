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

namespace Comely\Http\Common;

/**
 * Class HttpProp
 * This class is used to preserve case-sensitivity of the payload/headers keys
 * @package Comely\Http\Common
 */
class HttpProp
{
    /**
     * @param string $key
     * @param string|int|float|bool|array|null $value
     */
    public function __construct(public readonly string $key, public readonly string|int|float|bool|null|array $value)
    {
    }
}
