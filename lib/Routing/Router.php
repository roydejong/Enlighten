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
     * Initializes a new, blank router.
     */
    public function __construct()
    {
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
     * Dispatches a Request to a Route.
     *
     * @param Route $route
     * @param EnlightenContext $context
     * @return mixed Route target function return value, if any
     */
    public function dispatch(Route $route, EnlightenContext $context)
    {
        $targetFunc = null;
        $params = [];

        if ($route->isCallable()) {
            // A callable function that should be invoked directly
            $targetFunc = $route->getTarget();
            $targetFunc = $targetFunc->bindTo($context);
        } else {
            // A string path to a controller: resolve the controller and verify its validity
            throw new \Exception('Only callable route targets are currently implemented'); // TODO
        }

        // Inject the route variables into the arguments passed to the function
        $params = array_merge($params, $route->mapPathVariables($context->getRequest()));

        // Finally, invoke the specified controller function or the specified callable with the appropriate params
        return call_user_func_array($targetFunc, $params);
    }
}