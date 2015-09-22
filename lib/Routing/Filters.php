<?php

namespace Enlighten\Routing;

use Enlighten\Context;

/**
 * Manages a collection of routing filters.
 */
class Filters
{
    /**
     * Represents a filter that is triggered before a route is .
     * This filter can be applied to both application and route scope.
     */
    const BEFORE_ROUTE = 'beforeRoute';
    /**
     * Represents a filter that is triggered after a route completes.
     * This filter can be applied to both application and route scope.
     */
    const AFTER_ROUTE = 'afterRoute';
    /**
     * Represents a filter that is triggered before a route is invoked.
     * This filter can be applied to both application and route scope.
     */
    const ON_EXCEPTION = 'onException';
    /**
     * Represents a filter that is triggered when routing fails (404 error).
     * This filter only applies to application scope and not to route scope.
     */
    const NO_ROUTE_FOUND = 'noRouteFound';

    /**
     * A registry of event handlers.
     * This array contains arrays of filters one for each event type:
     * e.g. an "onExeption" filter will be reigstered to $handlers['onException'][0]
     *
     * @var array
     */
    protected $handlers;

    /**
     * Constructs a blank filter collection.
     */
    public function __construct()
    {
        $this->handlers = [];
    }

    /**
     * Registers a filter function.
     *
     * @param string $eventType The type of event, see constant values in Filters class. e.g. 'beforeRoute'.
     * @param callable $filter
     */
    public function register($eventType, callable $filter)
    {
        if (!isset($this->handlers[$eventType])) {
            $this->handlers[$eventType] = [];
        }

        $this->handlers[$eventType][] = $filter;
    }

    /**
     * Returns whether any filter functions have been registered for a given $eventType.
     *
     * @param string $eventType The type of event, e.g. 'beforeRoute'.
     * @return bool Returns whether any filter functions were registered or not.
     */
    public function anyHandlersForEvent($eventType)
    {
        return isset($this->handlers[$eventType]) && count($this->handlers[$eventType]) > 0;
    }

    /**
     * Triggers all filter functions for a given $eventType.
     *
     * @param string $eventType The type of event, e.g. 'beforeRoute'.
     * @param Context $context The optional context to be applied to the filter functions.
     * @return bool Returns whether execution should continue or not.
     */
    public function trigger($eventType, Context $context = null)
    {
        $continue = true;

        if (!isset($this->handlers[$eventType])) {
            return $continue;
        }

        foreach ($this->handlers[$eventType] as $filterFunction) {
            $params = [];

            if (!empty($context)) {
                $params = $context->determineParamValues($filterFunction);
            }

            $returnValue = call_user_func_array($filterFunction, $params);

            if ($returnValue === false) {
                $continue = false;
                break;
            }
        }

        return $continue;
    }
}