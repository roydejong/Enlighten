<?php

namespace Enlighten\Routing;

use Enlighten\Http\Request;

/**
 * Represents a route that maps an incoming request to an application code point.
 */
class Route
{
    /**
     * Matches a route against a request, and returns whether it is a good match or not.
     * This function result only implies a match and does not consider the importance / weight of a given route.
     *
     * @param Request $request
     * @return bool
     */
    public function matches(Request $request)
    {
        /**
         * TODO
         */
        return false;
    }
}