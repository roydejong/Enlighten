<?php

namespace Enlighten\Routing;

use Enlighten\Context;
use Enlighten\Http\Request;

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
     * Dispatches a Route, executing its action.
     *
     * @param Route $route
     * @param Request $request
     * @return mixed Route target function return value, if any.
     */
    public function dispatch(Route $route, Request $request)
    {
        if (!empty($this->context)) {
            $this->context->registerInstance($route);
        }

        return $route->action($request, $this->context);
    }
}