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

namespace Comely\Http\Response;

use Comely\Buffer\Buffer;

/**
 * Class CurlResponse
 * @package Comely\Http\Response
 */
class CurlResponse extends AbstractResponse
{
    /**
     * CurlResponse constructor.
     * @param int $httpStatusCode
     */
    public function __construct(int $httpStatusCode)
    {
        $this->code = $httpStatusCode;
        parent::__construct();
    }

    /**
     * @return int
     */
    public function httpStatusCode(): int
    {
        return $this->code;
    }

    /**
     * @return Buffer
     */
    public function body(): Buffer
    {
        return $this->body;
    }
}
