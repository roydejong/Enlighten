<?php

namespace Enlighten\Routing;

use Enlighten\Context;
use Enlighten\Http\Request;
use Enlighten\Http\Response;

/**
 * Handles the registration and mcathing of Routes.
 *
 * @see Enlighten\Http\Request
 * @see Enlighten\\Routing\Route
 */
class Router
{
    /**
     * A collection of all registered routes.
     *
     * @var Route[]
     */
    protected $routes;

    /**
     * A subdirectory that should be ignored for all routes.
     *
     * @default null
     * @var string
     */
    protected $subdirectory;

    /**
     * An optional context that will be provided to any functions that are invoked via this router.
     *
     * @default null
     * @var Context
     */
    protected $context;

    /**
     * Initializes a new, blank router.
     */
    public function __construct()
    {
        $this->subdirectory = null;
        $this->routes = [];
        $this->context = null;
    }

    /**
     * Clears all registered routes.
     */
    public function clear()
    {
        $this->routes = [];
    }

    /**
     * Registers a route in this router instance.
     * Subsequent calls to $this->route() will then consider this new route when matching against a request.
     *
     * @param Route $route
     */
    public function register(Route $route)
    {
        $this->routes[] = $route;
    }

    /**
     * Creates and registers a new redirection route.
     *
     * @param string $from The route mask to match against. Supports variables.
     * @param string $to The static URL to redirect the user to. Supports variables.
     * @param bool $permanent If true, a HTTP 301 permanent redirect is used. Otherwise, a HTTP 302 temporary redirect is used (default).
     * @return Route Returns the Route that was created and registered.
     */
    public function createRedirect($from, $to, $permanent = false)
    {
        $route = new Route($from, function (Request $request, Response $response) use ($from, $to, $permanent) {
            // Map URL variables
            $urlVariables = VariableUrl::extractUrlVariables($request->getRequestUri(), $to);

            if (!empty($urlVariables)) {
                $to = VariableUrl::applyUrlVariables($to, $urlVariables);
            }

            // Perform redirect response
            $response->doRedirect($to, $permanent);
        });
        $this->register($route);
        return $route;
    }

    /**
     * Gets the base subdirectory for all requests processed by this router.
     *
     * @return string
     */
    public function getSubdirectory()
    {
        return $this->subdirectory;
    }

    /**
     * Sets the base subdirectory for all requests processed by this router.
     *
     * For example, if you set "/projects/one" as your subdirectory, the router will assume that all routes begin with
     * that value. A route for "/test.html" will then match against requests for "/projects/one/test.html".
     *
     * NB: You should not use a trailing slash in your subdirectory names.
     *
     * @param $subdirectory
     * @return $this
     */
    public function setSubdirectory($subdirectory)
    {
        $this->subdirectory = $subdirectory;

        foreach ($this->routes as $route) {
            $route->setSubdirectory($subdirectory);
        }

        return $this;
    }

    /**
     * Gets the application context that is provided to route actions and filters.
     *
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Sets the application context that is provided to route actions and filters.
     *
     * @param mixed $context
     * @return Router
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Gets whether this router is empty (i.e. has no registered routes).
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->routes);
    }

    /**
     * Attempts to map a given $request to a registered route.
     *
     * @param Request $request
     * @return Route|null Returns the matched Route, or null upon failure.
     */
    public function route(Request $request)
    {
        foreach ($this->routes as $route) {
            if ($route->matches($request)) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Gets all routing options for a given request.
     *
     * @param Request $request
     * @return Route[]
     */
    public function getOptions(Request $request)
    {
        $routes = [];

        foreach ($this->routes as $route) {
            if ($route->matchesPattern($request)) {
                $routes[] = $route;
            }
        }

        return $routes;
    }

    /**
     * Dispatches a Route, executing its action.
     *
     * @param Route $route The route to be executed.
     * @param Request $request The request information, if available. Used for mapping route variables.
     * @return mixed Route target function return value, if any.
     */
    public function dispatch(Route $route, Request $request = null)
    {
        $context = $this->context;

        if (empty($this->context) && !empty($request)) {
            // If we have no context, but do have a request, prepare a context to store path variables in.
            // Otherwise routing path variables would be lost for no good reason.
            $context = new Context();
            $context->registerInstance($request);
        }

        if (!empty($context)) {
            // If we have a context, ensure that the route is made available in it.
            $context->registerInstance($route);

            if (!empty($request)) {
                // If we have a request, map the path variables and pass them to the context as primitive types by name.
                // This will allow us to inject info from a route e.g. "/view/$userId" to a $userId variable.
                $pathVariables = VariableUrl::extractUrlVariables($request->getRequestUri(), $route->getPattern());

                foreach ($pathVariables as $name => $value) {
                    $context->registerVariable($name, $value);
                }
            }
        }

        return $route->action($context);
    }
}