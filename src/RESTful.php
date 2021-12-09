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

namespace Comely\Http;

use Comely\Buffer\Buffer;
use Comely\Http\Common\Headers;
use Comely\Http\Common\HttpMethod;
use Comely\Http\Common\ReadPayload;
use Comely\Http\Common\URL;
use Comely\Http\Exception\RESTfulException;
use Comely\Http\Router\AbstractController;
use Comely\Http\Router\Request;

/**
 * Class RESTful
 * @package Comely\Http
 */
class RESTful
{
    /**
     * @param Router $router
     * @param \Closure $closure
     * @return AbstractController
     * @throws Exception\RouterException
     * @throws RESTfulException
     */
    public static function Request(Router $router, \Closure $closure): AbstractController
    {
        // Check if URL not rewritten properly (i.e. called /index.php/some/controller)
        $url = $_SERVER["REQUEST_URI"] ?? "";
        if (preg_match('/^\/?[\w\-.]+\.php\//', $url)) {
            $url = explode("/", $url);
            unset($url[1]);
            $url = implode("/", $url);
        }

        // Prerequisites
        $url = new URL($url);
        $method = HttpMethod::fromString($_SERVER["REQUEST_METHOD"] ?? "");
        if (!$method) {
            throw new RESTfulException('Invalid/Unsupported HTTP method read from server.REQUEST_METHOD');
        }

        // Headers
        $headers = []; // Initiate Headers
        foreach ($_SERVER as $key => $value) {
            $key = explode("_", $key);
            if ($key[0] === "HTTP") {
                unset($key[0]);
                $key = array_map(function ($part) {
                    return ucfirst(strtolower($part));
                }, $key);

                $headers[implode("-", $key)] = $value;
            }
        }

        $headers = new Headers($headers);

        // Payload & Body
        $body = new Buffer();
        $payload = []; // Initiate payload
        $contentType = strtolower(trim(explode(";", $_SERVER["CONTENT_TYPE"] ?? "")[0]));

        // Ready query string
        if (isset($_SERVER["QUERY_STRING"])) {
            parse_str($_SERVER["QUERY_STRING"], $payload);
        }

        // Get input body from stream
        $params = null;
        $stream = file_get_contents("php://input");
        if ($stream) {
            $body->append($stream); // Append "as-is" (Un-sanitized) body to request
            switch ($contentType) {
                case "application/json":
                    try {
                        $json = json_decode($stream, true, flags: JSON_THROW_ON_ERROR);
                    } catch (\JsonException) {
                        throw new RESTfulException('Invalid JSON body received');
                    }

                    if (is_array($json)) {
                        $params = $json;
                    } elseif (is_scalar($json) || is_null($json)) {
                        $params = ["_json" => $json];
                    }

                    break;
                case "application/x-www-form-urlencoded":
                    parse_str($stream, $params);
                    break;
                case "multipart/form-data":
                    if ($method === HttpMethod::POST) {
                        $params = $_POST; // Simply use $_POST var;
                    }

                    break;
            }
        }

        if (is_array($params)) { // Merge body and URL params
            $payload = array_merge($params, $payload);
        }

        // Payload
        $payload = new ReadPayload($payload);

        // Bypass HTTP auth.
        $bypassAuth = false;
        if ($method === HttpMethod::OPTIONS) {
            $bypassAuth = true;
        }

        // Get Controller
        $controller = $router->request(new Request($method, $url, $headers, $payload, $body), $bypassAuth);

        // Callback Close
        call_user_func($closure, $controller);
        return $controller;
    }
}
