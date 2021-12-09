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

namespace Comely\Http\Router;

use Comely\Buffer\Buffer;
use Comely\Http\Common\Headers;
use Comely\Http\Common\HttpMethod;
use Comely\Http\Common\ReadPayload;
use Comely\Http\Common\URL;

/**
 * Class Request
 * @package Comely\Http
 */
class Request
{
    /** @var string|null */
    public readonly ?string $contentType;

    /**
     * @param HttpMethod $method
     * @param URL $url
     * @param Headers $headers
     * @param ReadPayload $payload
     * @param Buffer $body
     */
    public function __construct(
        public readonly HttpMethod $method,
        public readonly URL $url,
        public readonly Headers $headers,
        public readonly ReadPayload $payload,
        public readonly Buffer $body
    )
    {
        $this->contentType = $this->headers->has("content-type") ?
            trim(explode(";", $this->headers->get("content-type"))[0]) : null;
    }
}
