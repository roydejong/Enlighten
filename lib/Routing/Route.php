<?php

namespace Enlighten\Routing;

use Enlighten\Context;
use Enlighten\Http\Request;
use Enlighten\Http\Response;

/**
 * Represents a route that maps an incoming request to an application code point.
 */
class Route
{
    /**
     * The variable operator used for dependency injection.
     */
    const VARIABLE_SEP = '$';

    /**
     * A subdirectory for this route, effectively a prefix for all patterns we match against.
     *
     * @var string
     */
    protected $subdirectory;

    /**
     * The pattern that the request URI will be matched against.
     * Can contain dynamic variables (indicated by $) that will be injected as dependencies.
     *
     * @var string
     */
    protected $pattern;

    /**
     * The pattern that the request URI will be matched against, converted to Regex string.
     *
     * @var string
     */
    protected $regexPattern;

    /**
     * The target of this route.
     * Can be a string, referring to a function, optionally within a controller, which will be invoked.
     * Can be a callable function, which will be invoked.
     *
     * Target string format: "path\to\class@functionName"
     *
     * @var string|callable
     */
    protected $target;

    /**
     * A collection of constraints this route is subject to.
     * Each constraint is a callable function that should return true or false.
     *
     * @var callable[]
     */
    protected $constraints;

    /**
     * Filter actions for this route.
     *
     * @var Filters
     */
    protected $filters;

    /**
     * Constructs a new Route configuration.
     *
     * @param string $pattern
     * @param mixed $target
     */
    public function __construct($pattern, $target)
    {
        $this->pattern = $pattern;
        $this->regexPattern = VariableUrl::createRegexMask($this->pattern);
        $this->target = $target;
        $this->constraints = [];
        $this->filters = new Filters();
    }

    /**
     * Gets the subdirectory prefix for this route.
     *
     * @return string
     */
    public function getSubdirectory()
    {
        return $this->subdirectory;
    }

    /**
     * Sets the subdirectory prefix for this route.
     *
     * @param string $subdirectory
     * @return $this
     */
    public function setSubdirectory($subdirectory)
    {
        $this->subdirectory = $subdirectory;
        $this->regexPattern = VariableUrl::createRegexMask($subdirectory . $this->pattern);
        return $this;
    }

    /**
     * Returns the pattern for this route.
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Returns the target for this route.
     *
     * @return callable|string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Registers a new constraint to this route.
     * A constraint should be a callable function, which will be invoked with the Request as parameter.
     *
     * @param callable $constraint
     */
    public function addConstraint(callable $constraint)
    {
        $this->constraints[] = $constraint;
    }

    /**
     * Sets a list of acceptable HTTP method for this route to match.
     *
     * @param array $acceptableMethods
     */
    public function setAcceptableMethods(array $acceptableMethods)
    {
        $this->addConstraint(function (Request $request) use ($acceptableMethods) {
            return in_array($request->getMethod(), $acceptableMethods);
        });
    }

    /**
     * Returns whether the Route target is callable or not.
     *
     * @return bool
     */
    public function isCallable()
    {
        return is_callable($this->target);
    }

    /**
     * Matches a route against a request, and returns whether it is a good match or not.
     * This function result only implies a match and does not consider the importance / weight of a given route.
     *
     * @param Request $request
     * @return bool
     */
    public function matches(Request $request)
    {
        if (preg_match($this->regexPattern, $request->getRequestUri()) <= 0) {
            return false;
        }

        foreach ($this->constraints as $constraint) {
            if (!$constraint($request)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Adds a filter action that should be executed after this route's action completes.
     *
     * @param callable $filter
     * @return $this
     */
    public function after(callable $filter)
    {
        $this->filters->register(Filters::AFTER_ROUTE, $filter);
        return $this;
    }

    /**
     * Adds a filter action that should be executed before this route's action starts.
     *
     * @param callable $filter
     * @return $this
     */
    public function before(callable $filter)
    {
        $this->filters->register(Filters::BEFORE_ROUTE, $filter);
        return $this;
    }

    /**
     * Adds a filter action that should be executed when an exception occurs in this route's action.
     *
     * @param callable $filter
     * @return $this
     */
    public function onException(callable $filter)
    {
        $this->filters->register(Filters::ON_EXCEPTION, $filter);
        return $this;
    }

    /**
     * Executes this route's action.
     *
     * @param Context $context
     * @throws RoutingException For unsupported or invalid action configurations.
     * @throws \Exception If an Exception is raised during the route's action, and no onException filter is registered, the Exception will be rethrown here.
     * @return mixed Action return value
     */
    public function action(Context $context = null)
    {
        $targetFunc = null;
        $classObject = null;
        
        // Create a dummy context if needed
        if (empty($context)) {
            $context = new Context();
        }

        // Determine target function
        if ($this->isCallable()) {
            $targetFunc = $this->getTarget();
        } else {
            $targetFunc = $this->translateToClassCallable($context);
            $classObject = $targetFunc[0];
        }

        // Route filter: before function
        if (!$this->filters->trigger(Filters::BEFORE_ROUTE, $context)) {
            return null;
        }
        
        $returnValue = null;
        $continue = true;

        // Execute a "before" function, if it is defined in the target class
        if ($classObject != null) {
            $beforeCallable = [$classObject, 'before'];

            if (is_callable($beforeCallable)) {
                $returnValue = $this->executeAction($beforeCallable, $context);

                if ($returnValue && $returnValue instanceof Response) {
                    // A respone was sent by the "before" function, stop execution
                    $continue = false;
                }
            }
        }

        if ($continue) {
            // Invoke the specified controller function or the specified callable with the appropriate params
            $returnValue = $this->executeAction($targetFunc, $context);
        }

        // Route filter: After function
        $this->filters->trigger(Filters::AFTER_ROUTE, $context);
        return $returnValue;
    }

    /**
     * Executes a route action, given a callable and a context.
     * 
     * @param callable $targetFunc The target function to be executed.
     * @param Context $context The context the target function should be executed under (for dependency injection).
     * @return mixed The function's return value.
     * @throws \Exception If an exception is raised during execution, it will be rethrown.
     */
    private function executeAction(callable $targetFunc, Context $context)
    {
        $returnValue = null;

        $paramValues = $context->determineParamValues($targetFunc);

        try {
            $returnValue = call_user_func_array($targetFunc, $paramValues);
        } catch (\Exception $ex) {
            $context->registerInstance($ex);

            $this->filters->trigger(Filters::ON_EXCEPTION, $context);

            if (!$this->filters->anyHandlersForEvent(Filters::ON_EXCEPTION)) {
                // If this exception was unhandled, rethrow it so it can be handled in the global scope
                throw $ex;
            }
        }
        
        return $returnValue;
    }

    /**
     * Attempts to translate this route's target to a function within a controller class.
     *
     * @param Context $context
     * @return array
     * @throws RoutingException
     */
    private function translateToClassCallable(Context $context)
    {
        // Load the class and create an instance of it
        $targetParts = explode('@', strval($this->getTarget()), 2);
        $targetClass = $targetParts[0];
        $targetFuncName = count($targetParts) > 1 ? $targetParts[1] : 'action';

        if (!class_exists($targetClass, true)) {
            throw new RoutingException('Could not locate class: ' . $targetClass);
        }

        $classObj = null;

        // Invoke constructor with dependency injection
        $parameterList = $context->determineParamValuesForConstructor($targetClass);

        try {
            $reflection = new \ReflectionClass($targetClass);
            $classObj = $reflection->newInstanceArgs($parameterList);
        } catch (\TypeError $ex) {
            throw new RoutingException('Type error thrown when calling constructor on ' . $targetClass, 0, $ex);
        }

        // Verify target function and return a callable
        $targetFunc = [$classObj, $targetFuncName];

        if (!is_callable($targetFunc)) {
            throw new RoutingException('Route target function is not callable: ' . $this->getTarget());
        }

        return $targetFunc;
    }
}