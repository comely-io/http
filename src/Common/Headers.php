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
 * Class HeadersIn
 * @package Comely\Http\Common
 */
class Headers extends AbstractHttpData
{
    /**
     * @param array $headers
     */
    public function __construct(array $headers)
    {
        foreach ($headers as $key => $value) {
            if (is_string($key) && preg_match('/^[\w\-.]+$/', $key) && is_string($value)) {
                $this->setHeaderValue($key, $value);
            }
        }
    }

    /**
     * @param string $key
     * @param string $value
     * @return void
     */
    protected function setHeaderValue(string $key, string $value): void
    {
        // Sanitize header value
        $value = filter_var(
            trim($value),
            FILTER_UNSAFE_RAW,
            FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
        );

        if ($value) {
            $this->setHttpProp(new HttpProp($key, $value));
        }
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function get(string $key): ?string
    {
        return $this->getHttpProp($key)?->value;
    }
}
