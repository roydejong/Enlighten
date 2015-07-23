<?php

namespace Enlighten;

use Enlighten\Http\Request;
use Enlighten\Http\RequestMethod;
use Enlighten\Http\Response;
use Enlighten\Http\ResponseCode;
use Enlighten\Routing\Filters;
use Enlighten\Routing\Route;
use Enlighten\Routing\Router;

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
     * Represents a collection of global filters that have been registered on this application instance.
     *
     * @var Filters
     */
    protected $filters;

    /**
     * The current application context.
     *
     * @var Context
     */
    protected $context;

    /**
     * Indicates whether output buffering is currently active.
     *
     * @var boolean
     */
    private $isBuffering;

    /**
     * Initializes a new Enlighten application instance.
     */
    public function __construct()
    {
        $this->request = null;
        $this->response = null;
        $this->filters = new Filters();
        $this->context = new Context();
        $this->context->registerInstance($this);
        $this->isBuffering = false;
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
        $this->context->registerInstance($request);
    }

    /**
     * Sets the router that should be used to handle routing and request resolution duties.
     *
     * @param Router $router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
        $this->router->setContext($this->context);
        $this->context->registerInstance($router);
    }

    /**
     * Bootstraps the application state as necessary.
     */
    protected function beforeStart()
    {
        // If no explicit request was given (via Enlighten::setRequest), create one based on the current PHP globals
        if (empty($this->request)) {
            $this->setRequest(Request::extractFromEnvironment());
        }

        $this->bootstrapRouter();
    }

    /**
     * Bootstraps a default router, if no router is configured.
     */
    private function bootstrapRouter()
    {
        // If no user-defined router was supplied (via Enlighten::setRouter()), initialize the default implementation
        if (empty($this->router)) {
            $this->setRouter(new Router());
        }
    }

    /**
     * Based on the current configuration, begins handling the incoming request.
     * This function should result in data being output.
     *
     * @return Response The response that was sent.
     */
    public function start()
    {
        $this->beforeStart();

        // Begin output buffering and begin building the HTTP response
        $this->isBuffering = true;
        ob_start();

        $this->response = new Response();
        $this->context->registerInstance($this->response);

        try {
            // Dispatch the request to the router
            $this->filters->trigger(Filters::BEFORE_ROUTE, $this->context);

            $routingResult = $this->router->route($this->request);

            if ($routingResult != null) {
                $this->context->registerInstance($routingResult);
                $this->response->setResponseCode(ResponseCode::HTTP_OK);
                $this->dispatch($routingResult);
            } else {
                $this->prepareNotFoundResponse();
            }

            $this->filters->trigger(Filters::AFTER_ROUTE, $this->context);
        } catch (\Exception $ex) {
            $this->prepareErrorResponse($ex);
        } finally {
            $this->sendResponse();
        }

        return $this->response;
    }

    /**
     * Cleans the output buffer and builds a default HTTP 500 error page.
     * Invokes the appropriate filter if one is registered; otherwise falls back to a default message.
     *
     * @param \Exception $ex The exception we are handling.
     * @throws \Exception If unhandled by filters, the original exception will be rethrown.
     */
    private function prepareErrorResponse(\Exception $ex)
    {
        ob_clean();

        $this->response = new Response();
        $this->response->setResponseCode(ResponseCode::HTTP_INTERNAL_SERVER_ERROR);

        $this->context->registerInstance($ex);

        $rethrow = false;

        if (!$this->filters->trigger(Filters::ON_EXCEPTION, $this->context)) {
            // If this exception was completely unhandled, rethrow it so it appears as any old php exception
            $rethrow = true;
        }

        $this->finalizeOutputBuffer();

        if (empty($this->response->getBody())) {
            // If nothing was output, then at least present a default message to the user.
            $this->response->setBody('An unexpected error has occurred while processing your request.');
        }

        if ($rethrow) {
            throw $ex;
        }
    }

    /**
     * Triggers any "not found" filters and prepares an appropriate 404 error response.
     */
    private function prepareNotFoundResponse()
    {
        ob_clean();

        $this->response = new Response();
        $this->response->setResponseCode(ResponseCode::HTTP_NOT_FOUND);

        $this->filters->trigger(Filters::NO_ROUTE_FOUND, $this->context);

        $this->finalizeOutputBuffer();

        if (empty($this->response->getBody())) {
            // If nothing was output, then at least present a default message to the user.
            $this->response->setBody('Page not found.');
        }
    }

    /**
     * Cleans the output buffer if it is active, moves its contents to the response, and stops output buffering.
     */
    private function finalizeOutputBuffer()
    {
        // Clean out the output buffer to the response, and send the built-up response to the client
        if ($this->isBuffering) {
            $this->response->appendBody(ob_get_contents());
            $this->isBuffering = false;

            ob_end_clean();
        }
    }

    /**
     * Moves the output buffer to the response, and send the built-up response to the client.
     */
    private function sendResponse()
    {
        $this->finalizeOutputBuffer();

        if ($this->request->isHead()) {
            // Do not send a body for HEAD requests
            $this->response->setBody('');
        }

        $this->response->send();
    }

    /**
     * Dispatches a Route.
     *
     * @param Route $route
     */
    public function dispatch(Route $route)
    {
        $this->beforeStart();
        $this->router->dispatch($route, $this->request);
    }

    /**
     * Internal function to register a new route.
     * Will bootstrap the router if necessary.
     *
     * @param string $pattern The regex pattern to match requests against (supports dynamic variables).
     * @param mixed|string $target The target function or path for the route.
     * @param string $requestMethod The request method constraint to apply, or null for no method constraint.
     * @return Route The generated route.
     */
    private function registerRoute($pattern, $target, $requestMethod = null)
    {
        $this->bootstrapRouter();

        $route = new Route($pattern, $target);

        if ($requestMethod != null) {
            $route->requireMethod($requestMethod);
        }

        $this->router->register($route);

        return $route;
    }

    /**
     * Registers a route for all request methods.
     *
     * @param string $pattern The regex pattern to match requests against (supports dynamic variables).
     * @param mixed|string $target The target function or path for the route.
     * @return Route The generated route.
     */
    public function route($pattern, $target)
    {
        return $this->registerRoute($pattern, $target);
    }

    /**
     * Registers a route for the GET request method.
     *
     * @param string $pattern The regex pattern to match requests against (supports dynamic variables).
     * @param mixed|string $target The target function or path for the route.
     * @return Route The generated route.
     */
    public function get($pattern, $target)
    {
        return $this->registerRoute($pattern, $target, RequestMethod::GET);
    }

    /**
     * Registers a route for the POST request method.
     *
     * @param string $pattern The regex pattern to match requests against (supports dynamic variables).
     * @param mixed|string $target The target function or path for the route.
     * @return Route The generated route.
     */
    public function post($pattern, $target)
    {
        return $this->registerRoute($pattern, $target, RequestMethod::POST);
    }

    /**
     * Registers a route for the PUT request method.
     *
     * @param string $pattern The regex pattern to match requests against (supports dynamic variables).
     * @param mixed|string $target The target function or path for the route.
     * @return Route The generated route.
     */
    public function put($pattern, $target)
    {
        return $this->registerRoute($pattern, $target, RequestMethod::PUT);
    }

    /**
     * Registers a route for the PATCH request method.
     *
     * @param string $pattern The regex pattern to match requests against (supports dynamic variables).
     * @param mixed|string $target The target function or path for the route.
     * @return Route The generated route.
     */
    public function patch($pattern, $target)
    {
        return $this->registerRoute($pattern, $target, RequestMethod::PATCH);
    }

    /**
     * Registers a route for the HEAD request method.
     *
     * @param string $pattern The regex pattern to match requests against (supports dynamic variables).
     * @param mixed|string $target The target function or path for the route.
     * @return Route The generated route.
     */
    public function head($pattern, $target)
    {
        return $this->registerRoute($pattern, $target, RequestMethod::HEAD);
    }

    /**
     * Registers a route for the OPTIONS request method.
     *
     * @param string $pattern The regex pattern to match requests against (supports dynamic variables).
     * @param mixed|string $target The target function or path for the route.
     * @return Route The generated route.
     */
    public function options($pattern, $target)
    {
        return $this->registerRoute($pattern, $target, RequestMethod::OPTIONS);
    }

    /**
     * Registers a route for the DELETE request method.
     *
     * @param string $pattern The regex pattern to match requests against (supports dynamic variables).
     * @param mixed|string $target The target function or path for the route.
     * @return Route The generated route.
     */
    public function delete($pattern, $target)
    {
        return $this->registerRoute($pattern, $target, RequestMethod::DELETE);
    }

    /**
     * Marks the application as being in a subdirectory. This affects how routes are matched.
     *
     * For example, if you set "/projects/one" as your subdirectory, the router will assume that all routes begin with
     * that value. A route for "/test.html" will then match against requests for "/projects/one/test.html".
     *
     * NB: You should not use a trailing slash in your subdirectory names.
     *
     * @param string $subdirectory
     * @return $this
     */
    public function setSubdirectory($subdirectory)
    {
        $this->bootstrapRouter();
        $this->router->setSubdirectory($subdirectory);
        return $this;
    }

    /**
     * Registers a filter function to be executed after application routing and execution completes, but before the response is sent.
     *
     * @param callable $filter Callable filter function.
     * @return $this
     */
    public function after(callable $filter)
    {
        $this->filters->register(Filters::AFTER_ROUTE, $filter);
        return $this;
    }

    /**
     * Registers a filter function to be executed before application routing logic begins.
     *
     * @param callable $filter Callable filter function.
     * @return $this
     */
    public function before(callable $filter)
    {
        $this->filters->register(Filters::BEFORE_ROUTE, $filter);
        return $this;
    }

    /**
     * Registers a filter function to be executed when an uncaught exception occurs during execution.
     *
     * @param callable $filter Callable filter function.
     * @return $this
     */
    public function onException(callable $filter)
    {
        $this->filters->register(Filters::ON_EXCEPTION, $filter);
        return $this;
    }

    /**
     * Registers a filter function that is called when routing fails (404 error).
     *
     * @param callable $filter
     * @return $this
     */
    public function notFound(callable $filter)
    {
        $this->filters->register(Filters::NO_ROUTE_FOUND, $filter);
        return $this;
    }
}