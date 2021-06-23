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

namespace Comely\Http\Query;

use Comely\Buffer\AbstractByteArray;
use Comely\Utils\ASCII;

/**
 * Class Payload
 * @package Comely\Http\Query
 */
class Payload extends AbstractDataIterator
{
    /**
     * @return Payload
     */
    public function flush(): self
    {
        $this->data = [];
        $this->count = 0;
        return $this;
    }

    /**
     * @param array $data
     * @return int
     */
    public function use(array $data): int
    {
        $added = 0;
        foreach ($data as $key => $value) {
            $this->set(strval($key), $value);
            $added++;
        }

        return $added;
    }

    /**
     * @param string $key
     * @param $value
     * @return Payload
     */
    public function set(string $key, $value): self
    {
        // Key
        if (!preg_match('/^[\w\-.]+$/i', $key)) {
            throw new \InvalidArgumentException('Invalid HTTP payload key');
        }

        // Value
        $prop = null;
        if (is_scalar($value) || is_null($value)) {
            $prop = new DataProp($key, $value); // Scalar or NULL type
        } elseif (is_array($value) || is_object($value)) {
            if ($value instanceof AbstractByteArray) {
                $filtered = $value->toBase16(true);
            } else {
                try {
                    $filtered = json_decode(json_encode($value, JSON_THROW_ON_ERROR), true, flags: JSON_THROW_ON_ERROR);
                } catch (\JsonException) {
                    throw new \UnexpectedValueException(sprintf('JSON filter fail on prop value of type "%s"', gettype($value)));
                }
            }

            $prop = new DataProp($key, $filtered); // Safe array
        }

        if (!$prop) {
            throw new \UnexpectedValueException(
                sprintf('Cannot set Http Payload value of type "%s"', gettype($value))
            );
        }

        $this->setProp($prop);
        return $this;
    }

    /**
     * @param string $prop
     * @return string|int|float|array|bool|null
     */
    public function getUnsafe(string $prop): string|int|float|array|null|bool
    {
        $prop = $this->getProp($prop);
        return $prop?->value;
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
        if (is_string($value) && preg_match('/^-?[1-9][0-9]*$/', $value)) {
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
     * @param $in
     * @param string|null $strAllowLowChars
     * @param bool $strStripHigh
     * @return string|int|float|array|bool|null
     */
    public function sanitizeValue($in, ?string $strAllowLowChars = null, bool $strStripHigh = true): string|int|float|array|null|bool
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

                if (is_string($key) && !preg_match('/^[\w\-.]+$/', $key)) {
                    continue;
                }

                $sanitized[$key] = $this->sanitizeValue($value, $strAllowLowChars, $strStripHigh);
            }

            return $sanitized;
        }

        return null;
    }
}
