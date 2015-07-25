<?php

namespace Enlighten;

/**
 * Represents the context for a routing process.
 * This is used to provide a target function with the appropriate data.
 */
class Context
{
    /**
     * Contains a mapping of types and instances.
     * These instances will be provided to
     *
     * @var array
     */
    protected $instances;

    /**
     * Contains a mapping of class names that are weakly related.
     *
     * For example, if an \InvalidArgumentException is added via registerInstance(), this mapping will contain an item
     * that maps \Exception - its parent class - to \InvalidArgumentException.
     *
     * This mapping is used when we don't have an exact (strong) match in $instances.
     *
     * @var array
     */
    protected $weakLinks;

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

        $reflectionObject = new \ReflectionObject($object);
        $className = $reflectionObject->getName();

        // Register strong link in $instance
        $this->instances[$className] = $object;

        // Recursively determine and register weak links
        $determineParent = function (\ReflectionClass $class) use ($className, &$determineParent) {
            $parentClass = $class->getParentClass();

            if (!empty($parentClass)) {
                $this->weakLinks[$parentClass->getName()] = $className;
                $determineParent($parentClass);
            }
        };

        $determineParent($reflectionObject);
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
            /** @var \Closure $callable */
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
    public function determineParamValues(callable $callable)
    {
        $reflectionParams = $this->getReflectionParameters($callable);

        $paramList = [];

        foreach ($reflectionParams as $reflectionParam) {
            $paramList[] = $this->determineParamValue($reflectionParam);
        }

        return $paramList;
    }

    /**
     * Based on the current context, attempts to determine an appropriate value for a given parameter.
     *
     * @param \ReflectionParameter The function parameter to analyze and determine a value for.
     * @return mixed
     */
    private function determineParamValue(\ReflectionParameter $parameter)
    {
        $class = $parameter->getClass();

        // If this is a object we may be able to map it to something in our context
        if (!empty($class)) {
            $className = $class->getName();

            // Determine strong type-based link
            if (isset($this->instances[$className])) {
                return $this->instances[$className];
            }

            // Determine weak type-based link
            if (isset($this->weakLinks[$className])) {
                $lowerClassName = $this->weakLinks[$className];
                return $this->instances[$lowerClassName];
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