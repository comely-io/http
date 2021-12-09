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
use Comely\Http\Common\HttpMethod;
use Comely\Http\Common\ReadPayload;
use Comely\Http\Common\URL;
use Comely\Http\Common\WriteHeaders;
use Comely\Http\Common\WritePayload;
use Comely\Http\Exception\CurlRequestException;
use Comely\Http\Exception\CurlResponseException;
use Comely\Http\Http;
use Comely\Utils\OOP\Traits\NoDumpTrait;
use Comely\Utils\OOP\Traits\NotCloneableTrait;
use Comely\Utils\OOP\Traits\NotSerializableTrait;

/**
 * Class CurlQuery
 * @package Comely\Http\Curl
 */
class CurlQuery
{
    /** @var HttpMethod */
    public readonly HttpMethod $method;
    /** @var URL */
    public readonly URL $url;
    /** @var WriteHeaders */
    public readonly WriteHeaders $headers;
    /** @var WritePayload */
    public readonly WritePayload $payload;
    /** @var Buffer */
    public readonly Buffer $body;
    /** @var null|int */
    private ?int $httpVersion = null;
    /** @var null|string */
    private ?string $userAgent = null;
    /** @var null|Authentication */
    private ?Authentication $auth = null;
    /** @var null|SSL */
    private ?SSL $ssl = null;
    /** @var bool Send payload as application/json regardless of content-type */
    private bool $contentTypeJSON = false;
    /** @var bool Expect JSON body in response */
    private bool $expectJSON = false;
    /** @var bool If expectJSON is true, use this prop to ignore received content-type */
    private bool $expectJSON_ignoreResContentType = false;
    /** @var bool */
    private bool $debug = false;
    /** @var int|null */
    private ?int $timeOut = null;
    /** @var int|null */
    private ?int $connectTimeOut = null;

    use NoDumpTrait;
    use NotSerializableTrait;
    use NotCloneableTrait;

    /**
     * @param HttpMethod $method
     * @param URL $url
     */
    public function __construct(HttpMethod $method, URL $url)
    {
        $this->method = $method;
        $this->url = $url;
        $this->headers = new WriteHeaders([]);
        $this->payload = new WritePayload([]);
        $this->body = new Buffer();
    }

    /**
     * @param bool $trigger
     * @return CurlQuery
     */
    public function debug(bool $trigger): self
    {
        $this->debug = $trigger;
        return $this;
    }

    /**
     * @return Authentication
     */
    public function auth(): Authentication
    {
        if (!$this->auth) {
            $this->auth = new Authentication();
        }

        return $this->auth;
    }

    /**
     * @return SSL
     */
    public function ssl(): SSL
    {
        if (!$this->ssl) {
            $this->ssl = new SSL();
        }

        return $this->ssl;
    }

    /**
     * @param int $version
     * @return CurlQuery
     */
    public function useHttpVersion(int $version): self
    {
        if (!in_array($version, Http::HTTP_VERSIONS)) {
            throw new \OutOfBoundsException('Invalid query Http version');
        }

        $this->httpVersion = $version;
        return $this;
    }

    /**
     * @param string|null $agent
     * @return CurlQuery
     */
    public function userAgent(?string $agent = null): self
    {
        $this->userAgent = $agent;
        return $this;
    }

    /**
     * @return CurlQuery
     */
    public function contentTypeJSON(): self
    {
        $this->contentTypeJSON = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function ignoreSSL(): self
    {
        $this->ssl()->verify(false);
        return $this;
    }

    /**
     * @param string $username
     * @param string $password
     * @return $this
     */
    public function authBasic(string $username, string $password): self
    {
        $this->auth()->basic($username, $password);
        return $this;
    }

    /**
     * @param bool $ignoreReceivedContentType
     * @return CurlQuery
     */
    public function expectJSON(bool $ignoreReceivedContentType = false): self
    {
        $this->expectJSON = true;
        $this->expectJSON_ignoreResContentType = $ignoreReceivedContentType;
        return $this;
    }

    /**
     * @param int|null $timeOut
     * @param int|null $connectTimeout
     * @return $this
     */
    public function setTimeouts(?int $timeOut = null, ?int $connectTimeout = null): self
    {
        if ($timeOut > 0) {
            $this->timeOut = $timeOut;
        }

        if ($connectTimeout > 0) {
            $this->connectTimeOut = $connectTimeout;
        }

        if ($connectTimeout > $timeOut) {
            throw new \InvalidArgumentException('connectTimeout value cannot exceed timeOut');
        }

        return $this;
    }

    /**
     * @return CurlResponse
     * @throws CurlRequestException
     * @throws CurlResponseException
     */
    public function send(): CurlResponse
    {
        // Check URL
        if (!$this->url->scheme || !$this->url->host) {
            throw new CurlRequestException('Cannot create cURL request without URL scheme and host');
        }

        $ch = curl_init(); // Init cURL handler
        curl_setopt($ch, CURLOPT_URL, $this->url->complete); // Set URL
        if ($this->httpVersion) {
            curl_setopt($ch, CURLOPT_HTTP_VERSION, $this->httpVersion);
        }

        // SSL?
        if (strtolower($this->url->scheme) === "https") {
            call_user_func([$this->ssl(), "register"], $ch); // Register SSL options
        }

        // Content-type
        $contentType = $this->headers->has("content-type") ?
            trim(explode(";", $this->headers->get("content-type"))[0]) : null;

        // Payload
        switch ($this->method->toString()) {
            case "GET":
                curl_setopt($ch, CURLOPT_HTTPGET, 1);
                if ($this->payload->count()) {
                    $sep = $this->url->query ? "&" : "?"; // Override URL
                    curl_setopt($ch, CURLOPT_URL, $this->url->complete . $sep . http_build_query($this->payload->array()));
                }

                break;
            default:
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method->toString());
                $payloadIsJSON = $this->contentTypeJSON || $contentType === "application/json";
                $payload = $this->body->raw();
                if (!$payload) {
                    if ($this->payload->count()) {
                        $payload = $payloadIsJSON ?
                            json_encode($this->payload->array()) : http_build_query($this->payload->array());
                    }
                }

                if ($payload) {
                    // Content-type JSON
                    if ($payloadIsJSON) {
                        // Content-type header
                        if (!$this->headers->has("content-type")) {
                            $this->headers->set("Content-type", "application/json; charset=utf-8");
                        }

                        // Content-length header
                        if (!$this->headers->has("content-length")) {
                            $this->headers->set("Content-length", strval(strlen($payload)));
                        }
                    }

                    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                }

                break;
        }

        // Headers
        if ($this->headers->count()) {
            $httpHeaders = [];
            foreach ($this->headers->array() as $hn => $hv) {
                $httpHeaders[] = $hn . ": " . $hv;
            }

            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);
        }

        // Authentication
        if ($this->auth) {
            call_user_func([$this->auth, "register"], $ch);
        }

        // User agent
        if ($this->userAgent) {
            curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        }

        // Timeouts
        if ($this->timeOut) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeOut);
        }

        if ($this->connectTimeOut) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeOut);
        }

        // Response
        $responseHeaders = [];

        // Finalise request
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($ch, $line) use ($responseHeaders) {
            if (preg_match('/^[\w\-]+:/', $line)) {
                $header = preg_split('/:/', $line, 2);
                $name = trim(strval($header[0] ?? null));
                $value = trim(strval($header[1] ?? null));
                if ($name && $value) {
                    /** @noinspection PhpArrayUsedOnlyForWriteInspection */
                    $responseHeaders[$name] = $value;
                }
            }

            return strlen($line);
        });

        // Execute cURL request
        $body = curl_exec($ch);
        if ($body === false) {
            throw new CurlResponseException(
                sprintf('cURL error [%d]: %s', curl_error($ch), curl_error($ch))
            );
        }

        // Response code
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $responseType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        if (is_string($responseCode) && preg_match('/[0-9]+/', $responseCode)) {
            $responseCode = intval($responseCode); // In case HTTP response code is returned as string
        }

        if (!is_int($responseCode)) {
            throw new CurlResponseException('Could not retrieve HTTP response code');
        }

        // Close cURL resource
        curl_close($ch);

        // Response Payload
        $payload = [];
        $responseIsJSON = is_string($responseType) && str_contains($responseType, 'json') || $this->expectJSON;
        if ($responseIsJSON) {
            if (!$this->expectJSON_ignoreResContentType) {
                if (!is_string($responseType)) {
                    throw new CurlResponseException('Invalid "Content-type" header received, expecting JSON', $responseCode);
                }

                if (strtolower(trim(explode(";", $responseType)[0])) !== "application/json") {
                    throw new CurlResponseException(
                        sprintf('Expected "application/json", got "%s"', $responseType),
                        $responseCode
                    );
                }
            }

            // Decode JSON body
            try {
                $payload = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                if ($this->debug) {
                    throw new CurlResponseException('JSON decode error; ' . $e->getMessage());
                }

                throw new CurlResponseException('An error occurred while decoding JSON response body');
            }
        }

        // Final CurlResponse instance
        return new CurlResponse(
            new Headers($responseHeaders),
            new ReadPayload($payload),
            (new Buffer($body))->readOnly(),
            $responseCode
        );
    }
}
