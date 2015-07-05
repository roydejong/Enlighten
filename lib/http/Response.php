<?php

namespace roydejong\enlighten\http;

/**
 * The HTTP response sent back based on an incoming Request.
 * Each response is built up over time as the application is executed, and then sent on execution end.
 */
class Response
{
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
     * Sets a HTTP header to a certain value.
     *
     * @param string $key
     * @param string $value
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
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
     * Sends the HTTP headers to the client.
     * Causes output!
     */
    protected function sendHeaders()
    {
        header(ResponseCode::getMessageForCode($this->statusCode));

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