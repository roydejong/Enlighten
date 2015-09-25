<?php

use Enlighten\Context;
use Enlighten\Http\Request;
use Enlighten\Http\Response;
use Enlighten\Routing\RoutingException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

function sampleFunction($nullMeBro, $bogusParam = 'abc', Request $request = null)
{
    return $request->getRequestUri();
}

class ContextTest extends PHPUnit_Framework_TestCase
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

        $paramList = $context->determineParamValues($myFunction);

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

        $paramList = $context->determineParamValues($myFunction);

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

        $paramList = $context->determineParamValues($myFunction);

        $expectedParams = [
            null,
            'abc',
            $request
        ];

        $this->assertEquals($expectedParams, $paramList);
        $this->assertEquals('/hello', call_user_func_array($myFunction, $paramList));
    }

    public function testInjectionWithPrimitiveType()
    {
        $myFunction = [$this, 'sampleFunction'];

        $context = new Context();
        $context->registerVariable('bogusParam', 'hello!');

        $paramList = $context->determineParamValues($myFunction);

        $expectedParams = [
            null,
            'hello!',
            null
        ];

        $this->assertEquals($expectedParams, $paramList);
    }

    public function testInjectionWithMixedMethods()
    {
        $request = new Request();
        $request->setRequestUri('/hello');

        $myFunction = [$this, 'sampleFunction'];

        $context = new Context();
        $context->registerVariable('bogusParam', 'hello!');
        $context->registerInstance($request);

        $paramList = $context->determineParamValues($myFunction);

        $expectedParams = [
            null,
            'hello!',
            $request
        ];

        $this->assertEquals($expectedParams, $paramList);
        $this->assertEquals('/hello', call_user_func_array($myFunction, $paramList));
    }

    public function testShouldReturnNullForUnresolvedParam()
    {
        $myFunction = function (Exception $exception) {
            // ...
        };

        // Valid object signature, but cannot resolve in context, expecting NULL
        $context = new Context();
        $paramList = $context->determineParamValues($myFunction);
        $expectedParams = [
            null
        ];
        $this->assertEquals($expectedParams, $paramList);
    }

    public function sampleFunction($nullMeBro, $bogusParam = 'abc', Request $request = null)
    {
        if ($request == null) return null;
        return $request->getRequestUri();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage can only register an object instance
     */
    public function testExceptionWhenRegisteringPrimitiveTypesAsInstance()
    {
        $context = new Context();
        $context->registerInstance('bla');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage can only register primitive types
     */
    public function testExceptionWhenRegisteringObjectsByName()
    {
        $context = new Context();
        $context->registerVariable('bla', new \stdClass());
    }

    public function testWeakSubclasses()
    {
        $subClass = new RoutingException('Test');

        $context = new Context();
        $context->registerInstance($subClass);

        $myFunc = function (Exception $ex, RoutingException $ex2) {
            // ..
        };

        $expectedParams = [
            $subClass,
            $subClass
        ];
        $actualParams = $context->determineParamValues($myFunc);

        // We expect that both Exception and and RoutingException will resolve to the same object.
        // This is because there is no "stronger" match in this test.
        $this->assertEquals($expectedParams, $actualParams);
    }

    public function testMixedStrengthSubclasses()
    {
        $subClass = new \InvalidArgumentException();
        $parentClass = new Exception();

        $context = new Context();
        $context->registerInstance($subClass);
        $context->registerInstance($parentClass);

        $myFunc = function (Exception $ex, \InvalidArgumentException $ex2) {
            // ..
        };

        $expectedParams = [
            $parentClass,
            $subClass
        ];
        $actualParams = $context->determineParamValues($myFunc);

        // We expect that both Exception and and RoutingException will resolve to the same object.
        // This is because there is no "stronger" match in this test.
        $this->assertEquals($expectedParams, $actualParams);
    }

    public function testContextSelfReference()
    {
        $context = new Context();

        $myFunc = function (Context $ctx) {
            // ..
        };

        $expectedParams = [
            $context
        ];
        $actualParams = $context->determineParamValues($myFunc);
        $this->assertEquals($expectedParams, $actualParams);
    }

    public function testGetInstances()
    {
        $objOne = new \stdClass();
        $objTwo = new \InvalidArgumentException();

        $context = new Context();
        $context->registerInstance($objOne);
        $context->registerInstance($objTwo);

        $actualParams = $context->getRegisteredInstances();
        $this->assertEquals($actualParams[0], $context);
        $this->assertEquals($actualParams[1], $objOne);
        $this->assertEquals($actualParams[2], $objTwo);
    }

    public function testGetVariables()
    {
        $context = new Context();
        $context->registerVariable('test1', 'hello');
        $context->registerVariable('test2', 12.34);

        $this->assertEquals(['test1' => 'hello', 'test2' => 12.34], $context->getRegisteredVariables());
    }
}