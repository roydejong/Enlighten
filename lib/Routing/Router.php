<?php

namespace Enlighten\Routing;

use Enlighten\EnlightenContext;
use Enlighten\Http\Request;

/**
 * Handles the registration of Routes, and routing incoming Requests.
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
     * Initializes a new, blank router.
     */
    public function __construct()
    {
        $this->subdirectory = null;

        $this->clear();
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
     * @param EnlightenContext $context
     * @return mixed Route target function return value, if any.
     */
    public function dispatch(Route $route, EnlightenContext $context)
    {
        return $route->action($context);
    }
}