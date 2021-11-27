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

use Comely\Http\Exception\RESTfulException;
use Comely\Http\Router\AbstractController;

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
     * @throws Exception\HttpRequestException
     * @throws Exception\RouterException
     * @throws RESTfulException
     */
    public static function Request(Router $router, \Closure $closure): AbstractController
    {
        $method = $_SERVER["REQUEST_METHOD"] ?? "";
        $url = $_SERVER["REQUEST_URI"] ?? "";

        // Check if URL not rewritten properly (i.e. called /index.php/some/controller)
        if (preg_match('/^\/?[\w\-.]+\.php\//', $url)) {
            $url = explode("/", $url);
            unset($url[1]);
            $url = implode("/", $url);
        }

        $req = new Request($method, $url);

        // Headers
        foreach ($_SERVER as $key => $value) {
            $key = explode("_", $key);
            if ($key[0] === "HTTP") {
                unset($key[0]);
                $key = array_map(function ($part) {
                    return ucfirst(strtolower($part));
                }, $key);

                try {
                    $req->headers()->set(implode("-", $key), $value);
                } catch (\Exception) {
                }
            }
        }

        // Payload
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
            $req->body()->append($stream); // Append "as-is" (Un-sanitized) body to request
            switch ($contentType) {
                case "application/json":
                    $json = json_decode($stream, true);
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
                    if (strtolower($method) === "post") {
                        $params = $_POST; // Simply use $_POST var;
                    }

                    break;
            }
        }

        if (is_array($params)) { // Merge body and URL params
            $payload = array_merge($params, $payload);
        }

        // Set to payload
        foreach ($payload as $key => $value) {
            try {
                $req->payload()->set(strval($key), $value);
            } catch (\Exception) {
            }
        }

        // Bypass HTTP auth.
        $bypassAuth = false;
        if ($req->method() === "OPTIONS") {
            $bypassAuth = true;
        }

        // Get Controller
        $controller = $router->request($req, $bypassAuth);

        // Callback Close
        call_user_func($closure, $controller);
        return $controller;
    }
}
