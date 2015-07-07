<?php

namespace Enlighten;

use Enlighten\Http\Request;
use Enlighten\Http\Response;

/**
 * Contains application state information.
 * Any controllers and closures should be based on this class so they can access this information.
 */
class EnlightenContext
{
    /**
     * Contains the request being processed.
     *
     * @var Request
     */
    protected $request;

    /**
     * Contains the response being built.
     *
     * @var Response
     */
    protected $response;

    /**
     * Gets the request being processed.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Gets the response being built.
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Sets this context's response.
     * This function should only be called when the context is created.
     *
     * @param Request $request
     */
    public function _setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Sets this context's response.
     * This function should only be called when the context is created.
     *
     * @param Response $response
     */
    public function _setResponse(Response $response)
    {
        $this->response = $response;
    }
}