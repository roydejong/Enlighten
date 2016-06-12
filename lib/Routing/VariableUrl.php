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
     * Extracts variables from a $requestUri, based on a variable URL pattern.
     *
     * @param string $requestUri
     * @param string $urlPattern
     * @return array A key-value array containing the extracted variables.
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

    /**
     * Given a $urlPattern, replaces variables in that URL with those contained in a given $variables set.
     *
     * For example, given the following $urlPattern and a $variables array of ['myVar' => 'replaced']:
     *  /example/$myVar/bla
     *
     * The following output would be generated:
     *  /example/replaced/bla
     *
     * @param $urlPattern
     * @param array $variables
     * @return string The mapped URL
     * @throws \InvalidArgumentException Throws an exception if a variable is missing from the $variables set
     */
    public static function applyUrlVariables($urlPattern, array $variables)
    {
        $inputParts = explode('/', $urlPattern);
        $resultUrl = '';
        $first = true;

        foreach ($inputParts as $inputPart) {
            if (!$first) {
                $resultUrl .= '/';
            } else {
                $first = false;
            }

            if (strpos($inputPart, self::T_URL_VARIABLE) === 0) {
                // URL variable to be replaced
                $variableName = substr($inputPart, 1);
                
                if (!isset($variables[$variableName])) {
                    throw new \InvalidArgumentException('applyUrlVariables(): the given $variables set does not contain requested URL variable: ' . $inputPart);
                }
                
                $resultUrl .= $variables[$variableName];
            } else {
                $resultUrl .= $inputPart;
            }
        }

        return $resultUrl;
    }
}