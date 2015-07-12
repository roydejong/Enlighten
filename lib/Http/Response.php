<?php

namespace Enlighten\Http;

/**
 * The HTTP response sent back based on an incoming Request.
 * Each response is built up over time as the application is executed, and then sent on execution end.
 */
class Response
{
    /**
     * The protocol that will be specified when sending response headers.
     * This is a constant value based on the HTTP spec that has been implemented in the framework code.
     *
     * @var string
     */
    const PROTOCOL_VERSION = 'HTTP/1.1';

    /**
     * The HTTP status code to be sent to the client.
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
     * The cookies to be sent to the client.
     * Key/value array containing name => Cookie.
     *
     * @var Cookie[]
     */
    protected $cookies;

    /**
     * Raw body string to be sent to the client.
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
        $this->cookies = [];
        $this->body = '';
    }

    /**
     * Sets the HTTP response code.
     *
     * @param int $code
     * @return $this
     */
    public function setResponseCode($code)
    {
        if (!ResponseCode::isValid($code)) {
            throw new \InvalidArgumentException('setResponseCode(): Invalid HTTP response code given of value ' . strval($code));
        }

        $this->statusCode = $code;
        return $this;
    }

    /**
     * Gets the HTTP response status code.
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
     * @return $this
     */
    public function doRedirect($url, $permanent = false)
    {
        $this->setResponseCode($permanent ? ResponseCode::HTTP_MOVED_PERMANENTLY : ResponseCode::HTTP_TEMPORARY_REDIRECT);
        $this->setHeader('Location', $url);
        return $this;
    }

    /**
     * Sets a HTTP header to a certain value.
     *
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function setHeader($key, $value)
    {
        $this->headers[strval($key)] = strval($value);
        return $this;
    }

    /**
     * Gets the current value of a given header.
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
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = strval($body);
        return $this;
    }

    /**
     * Appends to the response body.
     *
     * @param string $body
     * @return $this
     */
    public function appendBody($body)
    {
        $this->body .= strval($body);
        return $this;
    }

    /**
     * Gets the current value of the response body.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Creates or updates a cookie.
     *
     * @param string $name Cookie name.
     * @param string $value Cookie value.
     * @param int $expire Unix expiration timestamp, or 0 for session expire.
     * @param string|null $path The path the cookie can be used on.
     * @param string|null $domain The domain the cookie can be used on.
     * @param bool $secure True for HTTPS only.
     * @param bool $httpOnly True for HTTP(S) only (not accessible to JavaScript, etc).
     * @return $this
     */
    public function setCookie($name, $value, $expire = 0, $path = null, $domain = null, $secure = false, $httpOnly = false)
    {
        return $this->addCookie((new Cookie())
            ->setName($name)
            ->setValue($value)
            ->setExpireTimestamp($expire)
            ->setPath($path)
            ->setDomain($domain)
            ->setSecure($secure)
            ->setHttpOnly($httpOnly));
    }

    /**
     * Adds a cookie to be sent.
     *
     * @param Cookie $cookie
     * @return $this
     */
    public function addCookie(Cookie $cookie)
    {
        $this->cookies[$cookie->getName()] = $cookie;
        return $this;
    }

    /**
     * Sends the HTTP cookies to the client.
     * Causes output!
     */
    protected function sendCookies()
    {
        foreach ($this->cookies as $cookie) {
            setcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpireTimestamp(), $cookie->getPath(),
                $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
        }
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
        $this->sendCookies();

        if (ResponseCode::canHaveBody($this->statusCode)) {
            $this->sendBody();
        }
    }
}