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

use Comely\Utils\ASCII;

/**
 * Class ReadPayload
 * @package Comely\Http\Common
 */
class ReadPayload extends AbstractPayload
{
    /**
     * @param string $key
     * @return string|int|float|bool|array|null
     */
    public function getUnsafe(string $key): string|int|float|bool|null|array
    {
        return $this->getHttpProp($key)?->value;
    }

    /**
     * @param string $prop
     * @param string|null $allowLowChars
     * @param string|null $stripChars
     * @param bool $trim
     * @return string
     */
    public function getASCII(string $prop, ?string $allowLowChars = null, ?string $stripChars = null, bool $trim = true): string
    {
        $value = $this->getUnsafe($prop);
        if (!is_string($value)) {
            return "";
        }

        $value = ASCII::Filter($value, $allowLowChars, $stripChars);
        if ($trim) {
            $value = trim($value);
        }

        return $value;
    }

    /**
     * @param string $prop
     * @param string|null $strAllowLowChars
     * @param bool $strStripHigh
     * @return string|int|float|array|bool|null
     */
    public function getSanitized(string $prop, ?string $strAllowLowChars = null, bool $strStripHigh = true): string|int|float|array|null|bool
    {
        return $this->sanitizeValue($this->getUnsafe($prop), $strAllowLowChars, $strStripHigh);
    }

    /**
     * @param string $prop
     * @param bool $unSigned
     * @return int|null
     */
    public function getInt(string $prop, bool $unSigned = false): ?int
    {
        $value = $this->getUnsafe($prop);
        if (is_string($value) && preg_match('/^(0|-?[1-9][0-9]*)$/', $value)) {
            $value = intval($value);
        }

        if (is_int($value)) {
            if ($unSigned) {
                return $value >= 0 ? $value : null;
            }

            return $value;
        }

        return null;
    }

    /**
     * @param mixed $in
     * @param string|null $strAllowLowChars
     * @param bool $strStripHigh
     * @return string|int|float|bool|array|null
     */
    private function sanitizeValue(mixed $in, ?string $strAllowLowChars = null, bool $strStripHigh = true): string|int|float|bool|null|array
    {
        if (is_string($in)) {
            if (!$strStripHigh) {
                return filter_var($in, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
            }

            return trim(ASCII::Filter($in, allowLowChars: $strAllowLowChars));
        }

        if (is_scalar($in) || is_null($in)) {
            return $in;
        }

        if (is_array($in)) {
            $sanitized = [];
            foreach ($in as $key => $value) {
                if (!is_string($key) && !is_int($key)) {
                    continue;
                }

                if (is_string($key) && !preg_match('/^[\w\-+.]+$/', $key)) {
                    continue;
                }

                $sanitized[$key] = $this->sanitizeValue($value, $strAllowLowChars, $strStripHigh);
            }

            return $sanitized;
        }

        return null;
    }
}
