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

use Comely\Buffer\AbstractByteArray;

/**
 * Class Payload
 * @package Comely\Http\Common
 */
abstract class AbstractPayload extends AbstractHttpData
{
    /**
     * @param array $payload
     */
    public function __construct(array $payload)
    {
        foreach ($payload as $key => $value) {
            if (is_string($key) && preg_match('/^[\w\-+.]+$/', $key)) {
                $this->setPayloadVar($key, $value);
            }
        }
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    protected function setPayloadVar(string $key, mixed $value): void
    {
        // Value
        if (is_scalar($value) || is_null($value)) {
            $prop = new HttpProp($key, $value);
        } elseif (is_array($value) || is_object($value)) {
            if ($value instanceof AbstractByteArray) {
                $filtered = $value->toBase16(true);
            } else {
                try {
                    $filtered = json_decode(json_encode($value, JSON_THROW_ON_ERROR), true, flags: JSON_THROW_ON_ERROR);
                } catch (\JsonException) {
                    throw new \UnexpectedValueException(
                        sprintf('JSON filter fail on payload prop "%s" of type "%s"', $key, gettype($value))
                    );
                }
            }

            $prop = new HttpProp($key, $filtered);
        }

        if (!isset($prop)) {
            throw new \InvalidArgumentException(
                sprintf('Cannot set Http Payload value for "%s" of type "%s"', $key, gettype($value))
            );
        }

        $this->setHttpProp($prop);
    }
}
