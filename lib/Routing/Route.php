<?php

namespace Enlighten\Routing;

use Enlighten\Http\Request;
use Enlighten\Routing\Constraints\Constraint;

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
}