<?php

use Enlighten\Context;
use Enlighten\Http\Request;
use Enlighten\Http\Response;

function sampleFunction($nullMeBro, $bogusParam = 'abc', Request $request = null)
{
    return $request->getRequestUri();
}

class RoutingContextTest extends PHPUnit_Framework_TestCase
{
    /**
     * Determines whether this version of PHP is capable of determining default parameter values for closures via
     * reflection.
     *
     * @return bool
     */
    public function shouldSupportClosureDefaultValues()
    {
        if (defined('HHVM_VERSION')) {
            // Running HHVM; this should work
            return true;
        }

        // Confirmed not to work in PHP 5.5
        // Confirmed not to work in PHP 5.6
        // Confirmed not to work in PHP 7.0.0-dev nightly (tested 18 jul)
        return false;
    }

    public function testInjectionWithClosureFunc()
    {
        $request = new Request();
        $request->setRequestUri('/hello');

        $myFunction = function ($bogusParam = '123', Request $fillMe = null, Response $response = null) {
            return $fillMe->getRequestUri();
        };

        $context = new Context();
        $context->registerInstance($request);

        $paramList = $context->determineValues($myFunction);

        $expectedParams = [
            $this->shouldSupportClosureDefaultValues() ? '123' : null,
            $request,
            null
        ];

        // NB: Unfortunately, default values (e.g. "123" in the example above) in combination with closures are not
        // supported because of the way ReflectionParameter is implemented in PHP. Should that ever change, this test
        // will break (see "shouldSupportClosureDefaultValues"). HHVM does implement this.

        $this->assertEquals($expectedParams, $paramList);
        $this->assertEquals('/hello', call_user_func_array($myFunction, $paramList));
    }

    public function testInjectionWithStringFunc()
    {
        $request = new Request();
        $request->setRequestUri('/hello');

        $myFunction = 'sampleFunction';

        $context = new Context();
        $context->registerInstance($request);

        $paramList = $context->determineValues($myFunction);

        $expectedParams = [
            null,
            'abc',
            $request
        ];

        $this->assertEquals($expectedParams, $paramList);
        $this->assertEquals('/hello', call_user_func_array($myFunction, $paramList));
    }

    public function testInjectionWithArrayFunc()
    {
        $request = new Request();
        $request->setRequestUri('/hello');

        $myFunction = [$this, 'sampleFunction'];

        $context = new Context();
        $context->registerInstance($request);

        $paramList = $context->determineValues($myFunction);

        $expectedParams = [
            null,
            'abc',
            $request
        ];

        $this->assertEquals($expectedParams, $paramList);
        $this->assertEquals('/hello', call_user_func_array($myFunction, $paramList));
    }

    public function sampleFunction($nullMeBro, $bogusParam = 'abc', Request $request = null)
    {
        return $request->getRequestUri();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Must register an object instance
     */
    public function testExceptionWhenRegisteringPrimitiveTypes()
    {
        $context = new Context();
        $context->registerInstance('bla');
    }
}