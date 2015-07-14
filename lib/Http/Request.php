<?php

namespace Enlighten\Http;

/**
 * Represents an incoming HTTP request (read only).
 * Used for parsing and reading incoming user data.
 * Can also be used to mock incoming requests in unit tests.
 */
class Request
{
    /**
     * The request method (e.g. POST, POST, PUT...)
     *
     * @see Enlighten\Http\RequestMethod
     * @var string
     */
    protected $method;

    /**
     * The full requested URI, including query string.
     *
     * @var string
     */
    protected $uri;

    /**
     * Key/value array containing all posted values (i.e. $_POST).
     *
     * @var array
     */
    protected $post;

    /**
     * Key/value array containing all variables in the query string (i.e. $_GET).
     *
     * @var array
     */
    protected $query;

    /**
     * Key/value array containing all environment variables (i.e. $_SERVER).
     *
     * @var array
     */
    protected $environment;

    /**
     * Key/value array containing all cookies sent in the request (i.e. $_COOKIE).
     *
     * @var array
     */
    protected $cookies;

    /**
     * A collection of files that were uploaded in this request.
     * A key/value array of $id => $fileUpload.
     *
     * @var FileUpload[]
     */
    protected $fileUploads;

    /**
     * Initializes a new, blank HTTP request.
     */
    public function __construct()
    {
        $this->method = RequestMethod::GET;
        $this->uri = '/';
        $this->post = [];
        $this->query = [];
        $this->environment = [];
        $this->cookies = [];
        $this->fileUploads = [];
    }

    /**
     * Returns the HTTP request method.
     *
     * @see Enlighten\Http\RequestMethod
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Returns if this was a HTTP OPTIONS request.
     *
     * @return bool
     */
    public function isOptions()
    {
        return $this->getMethod() == RequestMethod::OPTIONS;
    }

    /**
     * Returns if this was a HTTP GET request.
     *
     * @return bool
     */
    public function isGet()
    {
        return $this->getMethod() == RequestMethod::GET;
    }

    /**
     * Returns if this was a HTTP POST request.
     *
     * @return bool
     */
    public function isPost()
    {
        return $this->getMethod() == RequestMethod::POST;
    }

    /**
     * Returns if this was a HTTP PUT request.
     *
     * @return bool
     */
    public function isPut()
    {
        return $this->getMethod() == RequestMethod::PUT;
    }

    /**
     * Returns if this was a HTTP PATCH request.
     *
     * @return bool
     */
    public function isPatch()
    {
        return $this->getMethod() == RequestMethod::PATCH;
    }

    /**
     * Returns if this was a HTTP HEAD request.
     *
     * @return bool
     */
    public function isHead()
    {
        return $this->getMethod() == RequestMethod::HEAD;
    }

    /**
     * Sets the HTTP request method.
     *
     * @see Enlighten\Http\RequestMethod
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Returns the request URI, optionally with query parameters.
     *
     * @param bool $includeParameters If true, the entire URI string will be returned, including query parameters.
     * @return string
     */
    public function getRequestUri($includeParameters = false)
    {
        $uri = $this->uri;

        if (!$includeParameters) {
            $sepIdx = strpos($uri, '?');

            if ($sepIdx !== false) {
                $uri = substr($uri, 0, $sepIdx);
            }
        }

        return $uri;
    }

    /**
     * Sets the full request URI, including query parameters.
     * NB: This function does not currently affect Request::$query.
     *
     * @param string $uri
     */
    public function setRequestUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * Returns a POSTed value by its $key.
     * Returns $defaultValue if the key is not found.
     *
     * If an array was posted with the given $key, this function will return $defaultValue.
     * To retrieve POSTed arrays, please use Request::getPostArray($key)
     *
     * @param string $key
     * @param null $defaultValue The value to return if $key is not found.
     * @return string|mixed A string value, or $defaultValue if the $key was not found.
     */
    public function getPost($key, $defaultValue = null)
    {
        if (isset($this->post[$key])) {
            $value = $this->post[$key];

            if (!is_array($value)) {
                return strval($value);
            }
        }

        return $defaultValue;
    }

    /**
     * Returns a POSTed array by its $key.
     * Returns NULL if the array could not found by its key, or if a non-array type was encountered.
     *
     * To retrieve POSTed values, rather than arrays, please use Request::getPost($key, $defaultValue)
     *
     * @param string $key
     * @return array|null
     */
    public function getPostArray($key)
    {
        if (isset($this->post[$key])) {
            $value = $this->post[$key];

            if (is_array($value)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Returns a key/value array of all posted data.
     *
     * @return array
     */
    public function getPostData()
    {
        return $this->post;
    }

    /**
     * Returns a query parameter value by its $key.
     * Returns $defaultValue if the key is not found.
     *
     * @param string $key
     * @param null $defaultValue The value to return if $key is not found.
     * @return string|mixed A string value, or $defaultValue if the $key was not found.
     */
    public function getQueryParam($key, $defaultValue = null)
    {
        if (isset($this->query[$key])) {
            $value = $this->query[$key];
            return strval($value);
        }

        return $defaultValue;
    }

    /**
     * Returns all query parameters as key/value array.
     *
     * @return array
     */
    public function getQueryParams()
    {
        return $this->query;
    }

    /**
     * Returns a environment parameter value by its $key.
     * Returns $defaultValue if the key is not found.
     *
     * @param string $key
     * @param null $defaultValue The value to return if $key is not found.
     * @return string|mixed A string value, or $defaultValue if the $key was not found.
     */
    public function getEnvironment($key, $defaultValue = null)
    {
        if (isset($this->environment[$key])) {
            $value = $this->environment[$key];
            return strval($value);
        }

        return $defaultValue;
    }

    /**
     * Returns all environment data as a key/value array.
     *
     * @return array
     */
    public function getEnvironmentData()
    {
        return $this->environment;
    }

    /**
     * Returns a cookie value value by its $key.
     * Returns $defaultValue if a cookie with the given $key was not found in the request.
     *
     * @param string $key
     * @param null $defaultValue The value to return if $key is not found.
     * @return string|mixed A string value, or $defaultValue if the $key was not found.
     */
    public function getCookie($key, $defaultValue = null)
    {
        if (isset($this->cookies[$key])) {
            $value = $this->cookies[$key];
            return strval($value);
        }

        return $defaultValue;
    }

    /**
     * Returns a key/value array of all cookies contained in this request.
     *
     * @return array
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * Gets a collection of uploaded files in this request.
     * Returns a key/value array of $id => $fileUpload.
     *
     * @return FileUpload[]
     */
    public function getFileUploads()
    {
        return $this->fileUploads;
    }

    /**
     * @param array $post Key/value $_POST array.
     */
    public function setPostData(array $post)
    {
        $this->post = $post;
    }

    /**
     * @param array $query Key/value $_GET array.
     */
    public function setQueryData(array $query)
    {
        $this->query = $query;
    }

    /**
     * @param array $environment Key/value $_SERVER array.
     */
    public function setEnvironmentData(array $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @param array $cookies
     */
    public function setCookieData(array $cookies)
    {
        $this->cookies = $cookies;
    }

    /**
     * @param array $files Key/value $_FILES array.
     */
    public function setFileData(array $files)
    {
        $this->fileUploads = [];

        foreach ($files as $key => $fileData) {
            $uploadObj = FileUpload::createFromFileArray($fileData);

            if (!empty($uploadObj)) {
                $this->fileUploads[$key] = $uploadObj;
            }
        }
    }

    /**
     * Creates a default Request based on the current PHP environment superglobals ($_SERVER, $_GET, $_POST, etc).
     */
    public static function extractFromEnvironment()
    {
        $getServerVar = function ($key) {
            return isset($_SERVER[$key]) ? $_SERVER[$key] : null;
        };

        $request = new Request();
        $request->setMethod($getServerVar('REQUEST_METHOD'));
        $request->setRequestUri($getServerVar('REQUEST_URI'));
        $request->setPostData($_POST);
        $request->setQueryData($_GET);
        $request->setEnvironmentData($_SERVER);
        $request->setCookieData($_COOKIE);
        $request->setFileData($_FILES);
        return $request;
    }
}