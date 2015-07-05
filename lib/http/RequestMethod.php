<?php

namespace roydejong\enlighten\http;

/**
 * HTTP request methods constants.
 */
abstract class RequestMethod
{
    /**
     * A request for available options and/or requirements associated with a resource.
     * Note: This request method is not typically used for web applications, APIs, etc.
     *
     * HTTP/1.1
     */
    const OPTIONS = 'OPTIONS';

    /**
     * A request to retrieve information.
     *
     * HTTP/1.1
     */
    const GET = 'GET';

    /**
     * A request to retrieve information, but without a message body.
     */
    const HEAD = 'HEAD';

    /**
     * A request
     */
    const POST = 'POST';
}