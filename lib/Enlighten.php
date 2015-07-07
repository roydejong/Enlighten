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
     * Initializes a new Enlighten application instance.
     */
    public function __construct()
    {
        $this->request = null;
        $this->response = null;
        $this->filters = new Filters();
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
            $this->setRequest(Request::extractFromEnvironment());
        }

        $this->_bootstrapRouter();
    }

    /**
     * Bootstraps a default router, if no router is configured.
     */
    private function _bootstrapRouter()
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
        ob_start();

        $this->response = new Response();

        try {
            // Dispatch the request to the router
            $this->filters->trigger(Filters::BeforeRoute);

            $routingResult = $this->router->route($this->request);

            if ($routingResult != null) {
                $this->response->setResponseCode(ResponseCode::HTTP_OK);
                $this->dispatch($routingResult);
            } else {
                $this->response->setResponseCode(ResponseCode::HTTP_NOT_FOUND);
                // TODO 404 error handling (#11)
            }

            // Clean out the output buffer to the response, and finally send the built-up response to the client
            $this->response->appendBody(ob_get_contents());
            ob_end_clean();

            if ($this->request->isHead()) {
                // Do not send a body for HEAD requests
                $this->response->setBody('');
            }

            $this->filters->trigger(Filters::AfterRoute);
        } catch (\Exception $ex) {
            $this->response->setResponseCode(ResponseCode::HTTP_INTERNAL_SERVER_ERROR);
            $this->filters->trigger(Filters::OnExeption, $ex);
        } finally {
            $this->response->send();
        }

        return $this->response;
    }

    /**
     * Dispatches a Route.
     *
     * @param Route $route
     */
    public function dispatch(Route $route)
    {
        $this->beforeStart();

        $this->router->dispatch($route, $this->createContext());
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
        $this->_bootstrapRouter();

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
     * Returns the current application context.
     * Contexts are used to provide components such as controllers and closures with the data they need to operate.
     * This function is typically only used internally by the framework when initializing closures and the like.
     *
     * @return EnlightenContext
     */
    public function createContext()
    {
        $context = new EnlightenContext();
        $this->fillContext($context);
        return $context;
    }

    /**
     * Fills a given $context with the current application state.
     * Contexts are used to provide components such as controllers and closures with the data they need to operate.
     * This function is typically only used internally by the framework when initializing controllers and the like.
     *
     * @param EnlightenContext $context
     */
    public function fillContext(EnlightenContext $context)
    {
        $context->_setRequest($this->request);
        $context->_setResponse($this->response);
    }

    /**
     * Registers a filter function to be executed after application routing and execution completes, but before the response is sent.
     *
     * @param callable $filter
     * @return $this
     */
    public function after(\Closure $filter)
    {
        $this->filters->register(Filters::AfterRoute, $filter);
        return $this;
    }

    /**
     * Registers a filter function to be executed before application routing logic begins.
     *
     * @param callable $filter
     * @return $this
     */
    public function before(\Closure $filter)
    {
        $this->filters->register(Filters::BeforeRoute, $filter);
        return $this;
    }

    /**
     * Registers a filter function to be executed when an uncaught exception occurs during execution.
     *
     * @param callable $filter
     * @return $this
     */
    public function onException(\Closure $filter)
    {
        $this->filters->register(Filters::OnExeption, $filter);
        return $this;
    }
}