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

namespace Comely\Http\Curl;

use Comely\Buffer\Buffer;
use Comely\Http\Common\Headers;
use Comely\Http\Common\ReadPayload;

/**
 * Class CurlResponse
 * @package Comely\Http\Curl
 */
class CurlResponse
{
    /**
     * @param Headers $headers
     * @param ReadPayload $payload
     * @param Buffer $body
     * @param int $statusCode
     */
    public function __construct(
        public readonly Headers $headers,
        public readonly ReadPayload $payload,
        public readonly Buffer $body,
        public readonly int $statusCode
    )
    {
    }
}
