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
 * Class WriteHeaders
 * @package Comely\Http\Common
 */
class WriteHeaders extends Headers
{
    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function set(string $key, string $value): self
    {
        $this->setHeaderValue($key, $value);
        return $this;
    }

    /**
     * @return void
     */
    public function flush(): void
    {
        $this->data = [];
        $this->count = 0;
    }
}
