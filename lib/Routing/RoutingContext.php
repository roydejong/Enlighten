<?php

namespace Enlighten\Routing;

/**
 * Represents the context for a routing process.
 * This is used to provide a target function with the appropriate data.
 */
class RoutingContext
{
    /**
     * Contains a mapping of types and instances.
     * These instances will be provided to
     *
     * @var array
     */
    protected $instances;

    /**
     * Initializes a blank routing context.
     */
    public function __construct()
    {
        $this->instances = [];
    }

    /**
     * Registers an object instance to the context.
     * If an instance with the same type is already registered, this will override it.
     *
     * @param $object
     */
    public function registerInstance($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('registerInstance(): Must register an object instance');
        }

        $className = get_class($object);
        $this->instances[$className] = $object;
    }

    /**
     * Given a callable function, attempts to determine the reflection parameters.
     *
     * @param callable $callable
     * @return \ReflectionParameter[]
     */
    private function getReflectionParameters(callable $callable)
    {
        $reflectionParams = [];

        if (is_array($callable)) {
            // Callable array
            $reflector = new \ReflectionMethod($callable[0], $callable[1]);
            $reflectionParams = $reflector->getParameters();
        } else if (is_string($callable)) {
            // Callable function string
            $reflector = new \ReflectionFunction($callable);
            $reflectionParams = $reflector->getParameters();
        } else if (is_a($callable, 'Closure') || is_callable($callable, '__invoke')) {
            $reflector = new \ReflectionObject($callable);
            $reflectionParams = $reflector->getMethod('__invoke')->getParameters();
        }

        return $reflectionParams;
    }

    /**
     * Given a callable function and the current context, attempt to determine the appropriate list of parameters to
     * pass to the function when it is called.
     *
     * @param callable $callable
     * @return array A list of parameter values to be passed to the function, in the appropriate order.
     */
    public function determineValues(callable $callable)
    {
        $reflectionParams = $this->getReflectionParameters($callable);

        $paramList = [];

        foreach ($reflectionParams as $reflectionParam) {
            $paramList[] = $this->determineValue($reflectionParam);
        }

        return $paramList;
    }

    /**
     * Based on the current context, attempts to determine an appropriate value for a given parameter.
     *
     * @param \ReflectionParameter The function parameter to analyze and determine a value for.
     * @return mixed
     */
    public function determineValue(\ReflectionParameter $parameter)
    {
        $class = $parameter->getClass();

        // If this is a object we may be able to map it to something in our context
        if (!empty($class)) {
            $className = $class->getName();

            if (isset($this->instances[$className])) {
                return $this->instances[$className];
            }
        }

        // We were unable to determine a suitable value based on this context. Pass back its default value if possible.
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        // As a final fallback we will simple pass NULL and let the function deal with it.
        return null;
    }
}