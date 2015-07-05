<?php

namespace roydejong\enlighten;

use roydejong\enlighten\http\Request;
use roydejong\enlighten\http\Response;
use roydejong\enlighten\http\ResponseCode;
use roydejong\enlighten\routing\Router;

/**
 * Represents an Enlighten application instance.
 * Responsible for manging the flow of an application.
 */
class Enlighten
{
    /**
     * The incoming HTTP request being handled by the application.
     * Can be set manually via setRequest(), or will be built automatically when Enlighten::start() is called.
     *
     * @var Request
     */
    protected $request;

    /**
     * The outgoing HTTP Response being sent by the application, as response to the Request.
     * This variable is assigned when Enlighten::start() is called, and sent to the client when execution completes.
     * Should not, and cannot, be accessed externally: only the core code and the invoked controller should ever do.
     *
     * @var Response
     */
    protected $response;

    /**
     * Represents the router used to match incoming requests, and resolve them to a controller.
     *
     * @var Router
     */
    protected $router;

    /**
     * Initializes a new Enlighten application instance.
     */
    public function __construct()
    {
        $this->request = null;
        $this->response = null;
    }

    /**
     * Sets a custom HTTP request to be processed by the application.
     * Must be called before Enlighten::start(), which is when it will be evaluated.
     *
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Sets the router that should be used to handle routing and request resolution duties.
     *
     * @param Router $router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Bootstraps the application state as necessary.
     */
    protected function beforeStart()
    {
        // If no explicit request was given (via Enlighten::setRequest), create one based on the current PHP globals
        if (empty($this->request)) {
            $this->setRequest(Request::createFromEnvironment());
        }

        // If no user-defined router was supplied (via Enlighten::setRouter()), initialize the default implementation
        if (empty($this->router)) {
            $this->setRouter(new Router());
        }
    }

    /**
     * Based on the current configuration, begins handling the incoming request.
     * This function should result in data being output.
     */
    public function start()
    {
        $this->beforeStart();

        // Begin output buffering and begin building the HTTP response
        ob_start();

        $this->response = new Response();

        // Dispatch the request to the router
        $routingResult = $this->router->route($this->request);

        if ($routingResult == null) {
            $this->response->setResponseCode(ResponseCode::HTTP_NOT_FOUND);
        }

        // Clean out the output buffer to the response, and finally send the built-up response to the client
        $this->response->appendBody(ob_get_contents());
        ob_end_clean();

        $this->response->send();

        // That's all folks! Execution has completed successfully.
        return true;
    }
}