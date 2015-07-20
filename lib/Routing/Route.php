<?php

namespace Enlighten\Routing;

use Enlighten\Context;
use Enlighten\EnlightenContext;
use Enlighten\Http\Request;

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
        $this->regexPattern = $this->formatRegex($this->pattern);
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
        $this->regexPattern = $this->formatRegex($subdirectory . $this->pattern);
        return $this;
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
     * Requires a certain HTTP method for this route to match.
     *
     * @param string $method
     */
    public function requireMethod($method)
    {
        $this->addConstraint(function (Request $request) use ($method) {
            return $request->getMethod() === $method;
        });
    }

    /**
     * Takes user pattern input and converts it to a properly formatted Regex pattern for matching against.
     *
     * @param string $pattern
     * @return string
     */
    private function formatRegex($pattern)
    {
        $parts = explode('/', $pattern);
        $formattedRegex = '';

        for ($i = 0; $i < count($parts); $i++) {
            if ($i > 0) {
                $formattedRegex .= "\/";
            }

            $part = $parts[$i];

            if (!empty($part) && $part[0] === self::VARIABLE_SEP) {
                $formattedRegex .= "[^\/]{1,}";
            } else {
                $formattedRegex .= $part;
            }
        }

        return '/^' . $formattedRegex . '$/';
    }

    /**
     * Maps the dynamic path variables in the path mask to the user input provided by a http request.
     * This function should only be launched if matches() returns true, otherwise results are unpredictable.
     *
     * @param Request $request
     * @return array
     */
    public function mapPathVariables(Request $request)
    {
        $inputParts = explode('/', $request->getRequestUri());
        $variableKeys = preg_grep('/^\$.+/', explode('/', $this->pattern));

        $params = array();

        foreach ($variableKeys as $key => $value) {
            $params[substr($value, 1)] = $inputParts[$key];
        }

        return $params;
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
    public function after(\Closure $filter)
    {
        $this->filters->register(Filters::AfterRoute, $filter);
        return $this;
    }

    /**
     * Adds a filter action that should be executed before this route's action starts.
     *
     * @param callable $filter
     * @return $this
     */
    public function before(\Closure $filter)
    {
        $this->filters->register(Filters::BeforeRoute, $filter);
        return $this;
    }

    /**
     * Adds a filter action that should be executed when an exception occurs in this route's action.
     *
     * @param callable $filter
     * @return $this
     */
    public function onException(\Closure $filter)
    {
        $this->filters->register(Filters::OnExeption, $filter);
        return $this;
    }

    /**
     * Executes this route's action.
     *
     * @param Request $request
     * @param Context $context
     * @throws RoutingException For unsupported or invalid action configurations.
     * @throws \Exception If an Exception is raised during the route's action, and no onException filter is registered, the Exception will be rethrown here.
     * @return mixed
     */
    public function action(Request $request, Context $context = null)
    {
        $targetFunc = null;

        if ($this->isCallable()) {
            // A callable function that should be invoked directly
            $targetFunc = $this->getTarget();
        } else {
            // A string path to a controller: resolve the controller and verify its validity
            $targetParts = explode('@', $this->getTarget(), 2);
            $targetClass = $targetParts[0];
            $targetFuncName = count($targetParts) > 1 ? $targetParts[1] : 'action';

            if (!class_exists($targetClass, true)) {
                throw new RoutingException('Could not locate class: ' . $targetClass);
            }

            $classObj = null;

            try {
                $classObj = new $targetClass();
            } catch (\Exception $ex) {
                throw new RoutingException('Exception thrown when calling default constructor on ' . $targetClass, 0, $ex);
            }

            $targetFunc = [$classObj, $targetFuncName];

            if (!is_callable($targetFunc)) {
                throw new RoutingException('Route target function is not callable: ' . $this->getTarget());
            }
        }

        // Perform dependency injection for the target function based on the Context
        $params = [];

        if (!empty($context)) {
            $params = $context->determineValues($targetFunc);
        }

        // Finally, invoke the specified controller function or the specified callable with the appropriate params
        $this->filters->trigger(Filters::BeforeRoute, $this);
        $retVal = null;

        try {
            $retVal = call_user_func_array($targetFunc, $params);
        } catch (\Exception $ex) {
            if (!$this->filters->trigger(Filters::OnExeption, $ex)) {
                // If this exception was unhandled, rethrow it so it can be handled in the global scope
                throw $ex;
            }
        }

        $this->filters->trigger(Filters::AfterRoute, $this);

        return $retVal;
    }
}