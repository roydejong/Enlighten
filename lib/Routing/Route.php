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
    public $pattern;

    /**
     * The target of this route.
     * Can be a string, referring to a function, optionally within a controller, which will be invoked.
     * Can be a callable function, which will be invoked.
     *
     * Target string format: "path\to\class@functionName"
     *
     * @var string|callable
     */
    public $target;

    /**
     * A collection of constraints this route is subject to.
     *
     * @var Constraint[]
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
        $this->pattern = $this->formatRegex($pattern);
        $this->target = $target;
        $this->constraints = [];
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
        if (preg_match($this->pattern, $request->getRequestUri()) <= 0) {
            return false;
        }

        foreach ($this->constraints as $constraint) {
            if (!$constraint->isSatisfied($request)) {
                return false;
            }
        }

        return false;
    }
}