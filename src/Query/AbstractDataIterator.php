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

/**
 * Class AbstractDataIterator
 * @package Comely\Http\Query
 */
abstract class AbstractDataIterator implements \Iterator, \Countable
{
    /** @var array */
    protected array $data = [];
    /** @var int */
    protected int $count = 0;

    /**
     * @param DataProp $prop
     * @return void
     */
    protected function setProp(DataProp $prop): void
    {
        $this->data[strtolower($prop->key)] = $prop;
        $this->count++;
    }

    /**
     * @param string $key
     * @return DataProp|null
     */
    protected function getProp(string $key): ?DataProp
    {
        return $this->data[strtolower($key)] ?? null;
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
        $array = [];
        /** @var DataProp $prop */
        foreach ($this->data as $prop) {
            $array[$prop->key] = $prop->value;
        }

        return $array;
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
     * @return string|int|float|array|null
     */
    final public function current(): string|int|float|array|null
    {
        /** @var DataProp $prop */
        $prop = current($this->data);
        return $prop->value;
    }

    /**
     * @return string
     */
    final public function key(): string
    {
        /** @var DataProp $prop */
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
