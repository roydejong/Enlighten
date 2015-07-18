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
     * Key/value array containing all the headers sent as part of this request.
     *
     * @var array[]
     */
    protected $headers;

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
        $this->headers = [];
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
     * Gets a HTTP header value by its $key.
     * This function is case-insensitive.
     * Returns $defaultValue if the key is not found.
     *
     * @param string $key Case-insensitive header name.
     * @param null $defaultValue The value to be returned if the given $key cannot be found.
     * @return null|string The array value as a string, or the given $defaultValue if the header was not found.
     */
    public function getHeader($key, $defaultValue = null)
    {
        // Make the headers lookup array and input $key lowercase, so we can use case-insensitive comparison.
        // We need to make a copy here because the original $headers should still contain the original casing.
        $key = strtolower($key);
        $headersCased = array_change_key_case($this->headers, CASE_LOWER);

        if (isset($headersCased[$key])) {
            $value = $headersCased[$key];
            return strval($value);
        }

        return $defaultValue;
    }

    /**
     * Gets all HTTP headers contained in this request.
     *
     * @return array Key => value array containing header names => values.
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Gets whether the page was requested on a secure (HTTPS) connection.
     *
     * @return bool True if HTTPS was used as protocol.
     */
    public function isHttps()
    {
        // $_SERVER['HTTPS'] contains a non-empty value if HTTPS was used.
        // For ISAPI with IIS, the value will be "off" instead.
        $value = $this->getEnvironment('HTTPS', null);
        return !empty($value) && strtolower($value) != 'off';
    }

    /**
     * Gets the protocol name used for this request.
     *
     * @return string Either "https" or "http" based on the request.
     */
    public function getProtocol()
    {
        return $this->isHttps() ? 'https' : 'http';
    }

    /**
     * Gets whether Ajax was used to issue this request, based on the X-Requested-With header.
     *
     * @return bool True if X-Requested-With equals XMLHttpRequest (case-insensitive).
     */
    public function isAjax()
    {
        return strtolower($this->getHeader('X-Requested-With', '')) === 'xmlhttprequest';
    }

    /**
     * Gets the user's remote IP address.
     *
     * This function does not consider X-Forwarded-For values as they can be spoofed.
     * As a result, the returned IP address may be that of a proxy server but it is correct and safe to use.
     *
     * @return string The user's IP address (may be IPv4 or IPv6 format).
     */
    public function getIp()
    {
        return $this->getEnvironment('REMOTE_ADDR', '127.0.0.1');
    }

    /**
     * Gets the referring page URL, if there is any.
     * This value is provided by the client and should be used with caution.
     *
     * @return string|null The referring page URL or NULL if no referrer is known.
     */
    public function getReferrer()
    {
        return $this->getHeader('Referrer', null);
    }

    /**
     * Gets the User Agent string provided, if there is one provided.
     * This value is provided by the client and should be used with caution.
     *
     * @return string|null The user agent string or NULL if was not provided.
     */
    public function getUserAgent()
    {
        return $this->getHeader('User-Agent', null);
    }

    /**
     * Gets the hostname used in this request.
     * This value is provided by the client and should be used with caution.
     *
     * @return string|null The hostname or NULL if it was not provided.
     */
    public function getHostname()
    {
        return $this->getHeader('Host', null);
    }

    /**
     * Returns whether the user's remote IP address is IPv6 or not.
     *
     * @return bool True if the user's IP address appears to be in IPv6 format.
     */
    public function isIpv6()
    {
        return strpos($this->getIp(), ':') !== false;
    }

    /**
     * Sets $_POST data for this request object.
     *
     * @param array $post Key/value $_POST array.
     */
    public function setPostData(array $post)
    {
        $this->post = $post;
    }

    /**
     * Sets $_GET data for this request object.
     *
     * @param array $query Key/value $_GET array.
     */
    public function setQueryData(array $query)
    {
        $this->query = $query;
    }

    /**
     * Sets $_SERVER data for this request object.
     *
     * @param array $environment Key/value $_SERVER array.
     */
    public function setEnvironmentData(array $environment)
    {
        $this->environment = $environment;
        $this->parseHeaders();
    }

    /**
     * Parses headers from the Environment data set in this request object.
     */
    private function parseHeaders()
    {
        $this->headers = [];

        // NB: We cannot use getallheaders() as it is Apache only, so we have to roll our own implementation.

        foreach ($this->environment as $key => $value) {
            if (!empty($value) && substr($key, 0, 5) == 'HTTP_') {
                $headerName = substr($key, 5); // Remove HTTP_ prefix from the name
                $headerName = str_replace('_', ' ', $headerName); // Swap underscores with space so we can use ucwords()
                $headerName = ucwords(strtolower($headerName)); // Convert casing to the standard notation (Bla-Bla)
                $headerName = str_replace(' ', '-', $headerName); // Swap spaces back for dashes

                $this->headers[$headerName] = $value;
            }
        }
    }

    /**
     * Sets $_COOKIES data for this request object.
     *
     * @param array $cookies
     */
    public function setCookieData(array $cookies)
    {
        $this->cookies = $cookies;
    }

    /**
     * Attempts to deflate a given $_FILES array.
     * When multiple files are uploaded in to the same form field, we need to process these as individual items.
     * This function also adds our custom "key" to the file array, which we will need further down the road.
     *
     * @param array $files
     * @return array
     */
    private function deflateFilesArray(array $files)
    {
        /**
         * This is where all the magic happens. This function converts a given $_FILES array to an array format that
         * the Enlighten request handling code can understand. The goal is simple: convert the array to a workable
         * format, rather than the complex maze the PHP developers created for us. :)
         *
         * A typical $_FILES array, with no multiple file uploads, may look like this:
         *
         * array {
         *    "upload" => array {
         *       "name" => "myfile.jpg",
         *       "tmp_path" => "/tmp/php5F.tmp",
         *       (...etc...)
         *    }
         * }
         *
         * When we combine multiple file uploads into one form field, however, this mess is given to us in $_FILES:
         *
         * array {
         *    "upload" => array {
         *       "name" => array {
         *          0 => "myfile.jpg",
         *          1 => "anotherfile.jpg",
         *       }
         *       "tmp_path" => array {
         *          0 => "/tmp/php5D.tmp",
         *          1 => "/tmp/php5E.tmp",
         *       }
         *       (...etc...)
         *    }
         * }
         *
         * This function tries to clean up this mess to ensure we get the following instead:
         *
         * array {
         *    0 => array {
         *       "key" => "upload"
         *       "name" => "myfile.jpg",
         *       "tmp_path" => "/tmp/php5D.tmp",
         *       (...etc...)
         *    },
         *    1 => array {
         *       "key" => "upload"
         *       "name" => "yourfile.jpg",
         *       "tmp_path" => "/tmp/php5E.tmp",
         *       (...etc...)
         *    }
         * }
         */

        $results = array();

        foreach ($files as $fileKey => $fileData) {
            // NB: $_FILES is structured by PHP so we do not need to be particularly careful - it is safe to assume
            // we can reliably predict

            if (is_array($fileData['name'])) {
                // This looks like a multi file array, try to simplify it
                $newFileItems = [];

                foreach ($fileData as $fieldName => $fieldValues) {
                    for ($i = 0; $i < count($fieldValues); $i++) {
                        if (!isset($newFileItems[$i])) {
                            $newFileItems[$i] = [
                                'key' => $fileKey
                            ];
                        }

                        $newFileItems[$i][$fieldName] = $fieldValues[$i];
                    }
                }

                foreach ($newFileItems as $newFileItem) {
                    $results[] = $newFileItem;
                }
            } else {
                // This does not look to be a fancy multi file array, no need to do extra work
                $fileData['key'] = $fileKey;
                $results[] = $fileData;
            }
        }

        return $results;
    }

    /**
     * @param array $files Key/value $_FILES array.
     */
    public function setFileData(array $files)
    {
        $files = $this->deflateFilesArray($files);

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