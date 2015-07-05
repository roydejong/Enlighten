<?php

namespace roydejong\enlighten\http;

/**
 * Represents an incoming HTTP request (read only).
 * Used for parsing and reading incoming user data.
 * Can also be used to mock incoming requests in unit tests.
 */
class Request
{
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
     * Initializes a new, blank HTTP request.
     */
    public function __construct()
    {
        $this->post = [];
        $this->query = [];
    }

    /**
     * Creates a default Request based on the current HTTP environment.
     */
    public static function createFromEnvironment()
    {
        $request = new Request();
        $request->post = $_POST;
        $request->query = $_GET;
        return $request;
    }
}