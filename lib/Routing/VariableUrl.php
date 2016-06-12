<?php

namespace Enlighten\Routing;

/**
 * Utility class for working with URLs that contain variables.
 */
class VariableUrl
{
    /**
     * The variable token used for variables in an URL pattern.
     */
    const T_URL_VARIABLE = '$';

    /**
     * Given a $urlPattern that optionally contains variables, returns a regex mask for matching request URIs against.
     *
     * @param $urlPattern
     * @return string
     */
    public static function createRegexMask($urlPattern)
    {
        $parts = explode('/', $urlPattern);
        $formattedRegex = '';

        for ($i = 0; $i < count($parts); $i++) {
            if ($i > 0) {
                $formattedRegex .= "\/";
            }

            $part = $parts[$i];

            if (!empty($part) && $part[0] === self::T_URL_VARIABLE) {
                $formattedRegex .= "[^\/]{1,}";
            } else {
                $formattedRegex .= $part;
            }
        }

        return '/^' . $formattedRegex . '$/';
    }

    /**
     * Extracts variables from a $requestUri, based on 
     *
     * @param string $requestUri
     * @param string $urlPattern
     * @return array
     */
    public static function extractUrlVariables($requestUri, $urlPattern)
    {
        $inputParts = explode('/', $requestUri);

        $regexPattern = '/^\\' . self::T_URL_VARIABLE . '.+/';
        $variableKeys = preg_grep($regexPattern, explode('/', $urlPattern));

        $params = array();

        foreach ($variableKeys as $key => $value) {
            $params[substr($value, 1)] = $inputParts[$key];
        }

        return $params;
    }
}