<?php

namespace Enlighten\Http;

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
     * A request to retrieve information (like a GET request), but without a message body.
     *
     * HTTP/1.1
     */
    const HEAD = 'HEAD';

    /**
     * A request is an operation to annotate, submit or extend a certain resource.
     *
     * HTTP/1.1
     */
    const POST = 'POST';

    /**
     * A request to store or overwrite a new resource.
     *
     * HTTP/1.1
     */
    const PUT = 'PUT';

    /**
     * A request to delete an existing resource.
     *
     * HTTP/1.1
     */
    const DELETE = 'DELETE';

    /**
     * A request to update or set certain values within an existing resources.
     *
     * IETF RFC 5789
     */
    const PATCH = 'PATCH';
}