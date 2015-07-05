<?php

namespace Enlighten\Http;

/**
 * The HTTP response sent back based on an incoming Request.
 * Each response is built up over time as the application is executed, and then sent on execution end.
 */
class Response
{
    const PROTOCOL_VERSION = 'HTTP/1.1';

    /**
     * The HTTP status code
     *
     * @var int
     */
    protected $statusCode;

    /**
     * Key/value array containing the headers to be sent to the client.
     *
     * @var array
     */
    protected $headers;

    /**
     * Raw body string.
     *
     * @var string
     */
    protected $body;

    /**
     * Initializes a new, blank response.
     */
    public function __construct()
    {
        $this->statusCode = ResponseCode::HTTP_OK;
        $this->headers = [];
        $this->body = '';
    }

    /**
     * @param int $code
     */
    public function setResponseCode($code)
    {
        if (!ResponseCode::isValid($code)) {
            throw new \InvalidArgumentException('setResponseCode(): Invalid HTTP response code given of value ' . strval($code));
        }

        $this->statusCode = $code;
    }

    /**
     * Returns the HTTP response status code.
     *
     * @return int
     */
    public function getResponseCode()
    {
        return $this->statusCode;
    }

    /**
     * Redirect the user to a specific URL.
     * Performs a HTTP 301 or HTTP 302 redirect.
     *
     * @param string $url
     * @param bool $permanent If true, a 301 Moved Permanently will be issued; otherwise a 307 Temporary Redirect.
     */
    public function doRedirect($url, $permanent = false)
    {
        $this->setResponseCode($permanent ? ResponseCode::HTTP_MOVED_PERMANENTLY : ResponseCode::HTTP_TEMPORARY_REDIRECT);
        $this->setHeader('Location', $url);
    }

    /**
     * Sets a HTTP header to a certain value.
     *
     * @param string $key
     * @param string $value
     */
    public function setHeader($key, $value)
    {
        $this->headers[strval($key)] = strval($value);
    }

    /**
     * Returns the current value of a given header.
     *
     * @param string $key
     * @return string|null The string value of the header, or null if the header was not set.
     */
    public function getHeader($key)
    {
        if (!isset($this->headers[$key])) {
            return null;
        }

        return $this->headers[$key];
    }

    /**
     * Sets the response body to a certain value.
     *
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = strval($body);
    }

    /**
     * Appends to the response body.
     *
     * @param string $body
     */
    public function appendBody($body)
    {
        $this->body .= strval($body);
    }

    /**
     * Returns the current value of the response body.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Sends the HTTP headers to the client.
     * Causes output!
     */
    protected function sendHeaders()
    {
        header(sprintf('%s %s', self::PROTOCOL_VERSION, ResponseCode::getMessageForCode($this->statusCode)));

        foreach ($this->headers as $name => $value) {
            header(sprintf("%s: %s", $name, $value));
        }
    }

    /**
     * Sends the response body to the client.
     * Causes output!
     */
    protected function sendBody()
    {
        echo $this->body;
    }

    /**
     * Sends the HTTP response to the client.
     * Causes output!
     */
    public function send()
    {
        $this->sendHeaders();

        if (ResponseCode::canHaveBody($this->statusCode)) {
            $this->sendBody();
        }
    }
}