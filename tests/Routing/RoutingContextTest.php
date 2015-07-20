<?php

use Enlighten\Http\Request;
use Enlighten\Http\Response;
use Enlighten\Routing\RoutingContext;

function sampleFunction($nullMeBro, $bogusParam = 'abc', Request $request = null)
{
    return $request->getRequestUri();
}

class RoutingContextTest extends PHPUnit_Framework_TestCase
{
    public function testRegisterAndPassObjectInstanceWithClosure()
    {
        $request = new Request();
        $request->setRequestUri('/hello');

        $myFunction = function ($bogusParam = '123', Request $fillMe = null, Response $response = null) {
            return $fillMe->getRequestUri();
        };

        $context = new RoutingContext();
        $context->registerInstance($request);

        $paramList = $context->determineValues($myFunction);

        $expectedParams = [
            null,
            $request,
            null
        ];

        // NB: Unfortunately, default values (e.g. "123" in the example above) in combination with closures are not
        // supported because of the way ReflectionParameter is implemented in PHP . Should that ever change, this test
        // will break.

        $this->assertEquals($expectedParams, $paramList);
        $this->assertEquals('/hello', call_user_func_array($myFunction, $paramList));
    }

    public function testFunctionByStringWithDefaultValues()
    {
        $request = new Request();
        $request->setRequestUri('/hello');

        $myFunction = 'sampleFunction';

        $context = new RoutingContext();
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

    public function testFunctionByArrayWithDefaultValues()
    {
        $request = new Request();
        $request->setRequestUri('/hello');

        $myFunction = [$this, 'sampleFunction'];

        $context = new RoutingContext();
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
        $context = new RoutingContext();
        $context->registerInstance('bla');
    }
}