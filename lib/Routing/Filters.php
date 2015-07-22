<?php

namespace Enlighten\Routing;

use Enlighten\Context;

/**
 * A collection of routing filters.
 */
class Filters
{
    /**
     * Represents a filter that is triggered before a route is .
     */
    const BEFORE_ROUTE = 'beforeRoute';
    /**
     * Represents a filter that is triggered after a route completes.
     */
    const AFTER_ROUTE = 'afterRoute';
    /**
     * Represents a filter that is triggered before a route is invoked.
     */
    const ON_EXCEPTION = 'onException';

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
     * @param Context $context The optional context to be applied to the filter functions.
     * @return bool Returns whether any functions were triggered or not.
     */
    public function trigger($eventType, Context $context = null)
    {
        if (!isset($this->handlers[$eventType])) {
            return false;
        }

        $any = false;

        foreach ($this->handlers[$eventType] as $filterFunction) {
            $params = [];

            if (!empty($context)) {
                $params = $context->determineValues($filterFunction);
            }

            call_user_func_array($filterFunction, $params);

            $any = true;
        }

        return $any;
    }
}