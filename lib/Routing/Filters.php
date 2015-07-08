<?php

namespace Enlighten\Routing;

/**
 * A collection of routing filters.
 */
class Filters
{
    /**
     * Represents a filter that is triggered before a route is .
     */
    const BeforeRoute = 'beforeRoute';
    /**
     * Represents a filter that is triggered after a route completes.
     */
    const AfterRoute = 'afterRoute';
    /**
     * Represents a filter that is triggered before a route is invoked.
     */
    const OnExeption = 'onException';

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
     * @param string $eventType The type of event, see constant values in Filters class. e.g. 'beforeRoute'
     * @param callable $filter
     */
    public function register($eventType, \Closure $filter)
    {
        if (!isset($this->handlers[$eventType])) {
            $this->handlers[$eventType] = [];
        }

        $this->handlers[$eventType][] = $filter;
    }

    /**
     * Triggers all filter functions for a given $eventType.
     *
     * @param string $eventType The type of event, e.g. 'beforeRoute'
     * @param mixed $eventArgs Event arguments to pass to the filter function.
     * @return bool Returns whether any functions were triggered or not.
     */
    public function trigger($eventType, $eventArgs = null)
    {
        if (!isset($this->handlers[$eventType])) {
            return false;
        }

        $any = false;

        foreach ($this->handlers[$eventType] as $filterFunction) {
            call_user_func($filterFunction, $eventArgs);

            $any = true;
        }

        return $any;
    }
}