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
 * Class AbstractDataObject
 * @package Comely\Http\Common
 */
abstract class AbstractHttpData implements \Iterator, \Countable
{
    /** @var array */
    protected array $data = [];
    /** @var int */
    protected int $count = 0;

    /**
     * @param HttpProp $prop
     * @return void
     */
    protected function setHttpProp(HttpProp $prop): void
    {
        $this->data[strtolower($prop->key)] = $prop;
        $this->count++;
    }

    /**
     * @param string $key
     * @return HttpProp|null
     */
    protected function getHttpProp(string $key): ?HttpProp
    {
        return $this->data[strtolower($key)] ?? null;
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function removeHttpProp(string $key): bool
    {
        $key = strtolower($key);
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
            $this->count--;
            return true;
        }

        return false;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists(strtolower($key), $this->data);
    }

    /**
     * @return array
     */
    final public function array(): array
    {
        $data = [];
        /** @var HttpProp $prop */
        foreach ($this->data as $prop) {
            $data[$prop->key] = $prop->value;
        }

        return $data;
    }

    /**
     * @return int
     */
    final public function count(): int
    {
        return $this->count;
    }

    /**
     * @return void
     */
    final public function rewind(): void
    {
        reset($this->data);
    }

    /**
     * @return string|int|float|bool|array|null
     */
    final public function current(): string|int|float|bool|null|array
    {
        /** @var HttpProp $prop */
        $prop = current($this->data);
        return $prop->value;
    }

    /**
     * @return string
     */
    final public function key(): string
    {
        /** @var HttpProp $prop */
        $prop = $this->data[key($this->data)];
        return $prop->key;
    }

    /**
     * @return void
     */
    final public function next(): void
    {
        next($this->data);
    }

    /**
     * @return bool
     */
    final public function valid(): bool
    {
        return !is_null(key($this->data));
    }
}
