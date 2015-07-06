<?php

use Enlighten\Http\Request;
use Enlighten\Http\RequestMethod;
use Enlighten\Routing\Route;

class RouteTest extends PHPUnit_Framework_TestCase
{
    public function testSimpleMatchingSuccess()
    {
        $route = new Route('/dir/sample.html', function () {
            // ...
        });

        $request = new Request();
        $request->setRequestUri('/dir/sample.html');
        $request->setMethod(RequestMethod::GET);

        $this->assertTrue($route->matches($request));
    }

    public function testSimpleMatchingFail()
    {
        $route = new Route('/example', function () {
            // ...
        });

        $request = new Request();
        $request->setRequestUri('/ex');
        $request->setMethod(RequestMethod::GET);

        $this->assertFalse($route->matches($request));
    }

    public function testBlankMatching()
    {
        $route = new Route('/', function () {
            // ...
        });

        $request = new Request();
        $request->setRequestUri('');
        $request->setMethod(RequestMethod::GET);

        $this->assertFalse($route->matches($request));
    }

    public function testShouldIgnoreQueryStrings()
    {
        $route = new Route('/', function () {
            // ...
        });

        $request = new Request();
        $request->setRequestUri('/?query=sample');
        $request->setMethod(RequestMethod::GET);

        $this->assertTrue($route->matches($request));
    }

    public function testIsCallable()
    {
        $route = new Route('/', function () {
            // ...
        });
        $this->assertTrue($route->isCallable(), 'Anonymous functions should be callable');

        $route = new Route('/', ['RouteTest', 'testIsCallable']);
        $this->assertTrue($route->isCallable(), 'Function references by array should be callable');

        $route = new Route('/', 'Enlighten\Enlighten');
        $this->assertFalse($route->isCallable(), 'String reference to class is not callable');
    }

    public function testVariableMapping()
    {
        $route = new Route('/view/user/$id/do/$action', function () {
            // ...
        });

        $request = new Request();
        $request->setRequestUri('/view/user/5/do/teststr_123.html');

        $this->assertTrue($route->matches($request));
        $this->assertEquals(['id' => '5', 'action' => 'teststr_123.html'], $route->mapPathVariables($request));
    }

    public function testConditionFailures()
    {
        $failureConstraint = function (Request $request) {
            // ...
            return false;
        };

        $route = new Route('/dir/sample.html', function () {
            // ...
        });
        $route->addConstraint($failureConstraint);

        $request = new Request();
        $request->setRequestUri('/dir/sample.html');
        $request->setMethod(RequestMethod::GET);

        $this->assertFalse($route->matches($request));
    }

    public function testConditionSuccess()
    {
        $postMessageConstraint = function (Request $request) {
            if ($request->getMethod() == RequestMethod::POST) {
                return true;
            }
            return false;
        };

        $route = new Route('/dir/sample.html', function () {
            // ...
        });
        $route->addConstraint($postMessageConstraint);

        $request = new Request();
        $request->setRequestUri('/dir/sample.html');
        $request->setMethod(RequestMethod::POST);

        $this->assertTrue($route->matches($request));
    }

    public function testRequireMethodCondition()
    {
        $route = new Route('/dir/sample.html', function () {
            // ...
        });
        $route->requireMethod(RequestMethod::PATCH);

        $request = new Request();
        $request->setRequestUri('/dir/sample.html');
        $request->setMethod(RequestMethod::POST);

        $this->assertFalse($route->matches($request));

        $request = new Request();
        $request->setRequestUri('/dir/sample.html');
        $request->setMethod(RequestMethod::PATCH);

        $this->assertTrue($route->matches($request));
    }
}
