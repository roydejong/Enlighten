<?php

namespace Enlighten\Http;

/**
 * Represents a HTTP cookie that can be transmitted to a client.
 * The client is responsible for enforcing these settings.
 */
class Cookie
{
    /**
     * Initializes a new, blank Cookie object with default settings.
     */
    public function __construct()
    {
        $this->name = '';
        $this->value = '';
        $this->expire = null;
        $this->path = null;
        $this->domain = null;
        $this->secure = false;
        $this->httpOnly = false;
    }

    /**
     * The name of the cookie.
     *
     * @default Empty string
     * @var string
     */
    protected $name;

    /**
     * The value of the cookie.
     *
     * @default Empty string
     * @var string
     */
    protected $value;

    /**
     * The date and time on which the cookie expires. This expiration is managed by the client.
     * If set to null, the cookie will expire at the end of the session (on browser close).
     *
     * @default null
     * @var \DateTime|null
     */
    protected $expire;

    /**
     * The path on the server in which the cookie will be available on.
     * If set to '/', the cookie will be available within the entire domain.
     * If set to '/foo/', the cookie will only be available within the /foo/ directory and all sub-directories.
     * The default value is the current directory that the cookie is being set in.
     *
     * @default null
     * @var string|null
     */
    protected $path;

    /**
     * The domain that the cookie is available to.
     * Setting the domain to 'www.example.com' will make the cookie available in www and higher subdomains.
     * Cookies available to a lower domain, such as 'example.com' will be available to higher subdomains, such as www.
     *
     * @default null
     * @var string|null
     */
    protected $domain;

    /**
     * Indicates that the cookie should only be transmitted over a secure HTTPS connection from the client.
     * When set to TRUE, the cookie will only be set if a secure connection exists.
     *
     * @default false
     * @var bool
     */
    protected $secure;

    /**
     * When TRUE, the cookie will be made accessible only through the HTTP protocol.
     * This means that the cookie won't be accessible by scripting languages, such as JavaScript.
     * It has been suggested that this setting can effectively help to reduce identity theft through XSS attacks.
     *
     * @default false
     * @var bool
     */
    protected $httpOnly;

    /**
     * Gets the name of the cookie.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name of the cookie.
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Gets the value of the cookie.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the value of the cookie.
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Gets the expiration date and time for this cookie.
     * May return null for cookies that expire on session end.
     *
     * @return \DateTime|null
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * Returns the expiration unix timestamp.
     * May return 0 if the cookie is set to expire on session end.
     *
     * @return int
     */
    public function getExpireTimestamp()
    {
        if (empty($this->expire)) {
            return 0;
        }

        return $this->expire->getTimestamp();
    }

    /**
     * Sets the time the cookie expires to a given DateTime value.
     *
     * @param \DateTime $expire
     * @return $this
     */
    public function setExpire(\DateTime $expire)
    {
        $this->expire = $expire;
        return $this;
    }

    /**
     * Sets the cookie's expire timestamp to a given unix timestamp.
     *
     * @param int $unixTimestamp
     * @return $this
     */
    public function setExpireTimestamp($unixTimestamp)
    {
        $this->expire = new \DateTime();
        $this->expire->setTimestamp($unixTimestamp);
        return $this;
    }

    /**
     * Sets the cookie's expiration so it will expire at the end of the browser session.
     *
     * @return $this
     */
    public function setExpireOnSession()
    {
        $this->expire = null;
        return $this;
    }

    /**
     * Gets the cookie path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets the cookie path.
     *
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Gets the cookie domain.
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Sets the cookie domain.
     *
     * @param string $domain
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * Gets whether the cookie should only be transmitted over a secure HTTPS connection.
     *
     * @return boolean
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * Sets whether the cookie should only be transmitted over a secure HTTPS connection
     *
     * @param boolean $secure
     * @return $this
     */
    public function setSecure($secure)
    {
        $this->secure = $secure;
        return $this;
    }

    /**
     * Gets whether the cookie will be made accessible only through the HTTP protocol.
     *
     * @return boolean
     */
    public function isHttpOnly()
    {
        return $this->httpOnly;
    }

    /**
     * Sets whether the cookie will be made accessible only through the HTTP protocol.
     *
     * @param boolean $httpOnly
     * @return $this
     */
    public function setHttpOnly($httpOnly)
    {
        $this->httpOnly = $httpOnly;
        return $this;
    }
}