<?php

use Enlighten\EnlightenContext;
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

    public function testRouteActionClosure()
    {
        $request = new Request();
        $request->setRequestUri('/dir/sample.html');
        $request->setMethod(RequestMethod::GET);

        $route = new Route('/dir/sample.html', function () use ($request) {
            // Our closure should receive our use variable ($request)
            // Our closure should also have access to the Context via $this
            echo $request->getMethod();
            echo $this->getRequest()->getMethod();
            return 'test';
        });
        $route->requireMethod(RequestMethod::GET);

        $context = new EnlightenContext();
        $context->_setRequest($request);

        $this->assertTrue($route->matches($request));

        $this->expectOutputString('GETGET');
        $this->assertEquals('test', $route->action($context));
    }

    public function testBeforeFilter()
    {
        $route = new Route('/dir/sample.html', function () {
            echo 'during';
        });

        $route->before(function () {
            echo 'before';
        });

        $request = new Request();
        $request->setRequestUri('/dir/sample.html');
        $request->setMethod(RequestMethod::GET);

        $this->assertTrue($route->matches($request));

        $context = new EnlightenContext();
        $context->_setRequest($request);

        $route->action($context);

        $this->expectOutputString('beforeduring');
    }

    public function testAfterFilter()
    {
        $route = new Route('/dir/sample.html', function () {
            echo 'during';
        });

        $route->after(function () {
            echo 'after';
        });

        $request = new Request();
        $request->setRequestUri('/dir/sample.html');
        $request->setMethod(RequestMethod::GET);

        $this->assertTrue($route->matches($request));

        $context = new EnlightenContext();
        $context->_setRequest($request);

        $route->action($context);

        $this->expectOutputString('duringafter');
    }

    /**
     * If an OnException filter is registered, the exception should be passed to any registered filters.
     * In this scenario, note that the Exception should not be thrown up to the global scope, it is "handled".
     */
    public function testExceptionFilter()
    {
        $route = new Route('/dir/sample.html', function () {
            throw new \Exception('TestEx');
        });

        $route->onException(function (\Exception $ex) {
            echo $ex->getMessage();
        });

        $request = new Request();
        $request->setRequestUri('/dir/sample.html');
        $request->setMethod(RequestMethod::GET);

        $this->assertTrue($route->matches($request));

        $context = new EnlightenContext();
        $context->_setRequest($request);

        $route->action($context);

        $this->expectOutputString('TestEx');
    }

    /**
     * If no onException filter is registered, the exceptions should be thrown so they can be handled in global scope.
     *
     * @expectedException RuntimeException
     */
    public function testUnfilteredException()
    {
        $route = new Route('/dir/sample.html', function () {
            throw new RuntimeException();
        });

        $request = new Request();
        $request->setRequestUri('/dir/sample.html');
        $request->setMethod(RequestMethod::GET);

        $this->assertTrue($route->matches($request));

        $context = new EnlightenContext();
        $context->_setRequest($request);

        $route->action($context);
    }

    public function testRegexPattern()
    {
        $route = new Route('/dir/(sample|example)(/?)', function () {
            // ...
        });

        $request = new Request();
        $request->setRequestUri('/dir/example/');
        $request->setMethod(RequestMethod::GET);
        $this->assertTrue($route->matches($request));

        $request = new Request();
        $request->setRequestUri('/dir/example');
        $request->setMethod(RequestMethod::GET);
        $this->assertTrue($route->matches($request));

        $request = new Request();
        $request->setRequestUri('/dir/sample/');
        $request->setMethod(RequestMethod::GET);
        $this->assertTrue($route->matches($request));

        $request = new Request();
        $request->setRequestUri('/dir/sample');
        $request->setMethod(RequestMethod::GET);
        $this->assertTrue($route->matches($request));

        $request = new Request();
        $request->setRequestUri('/dir/samples');
        $request->setMethod(RequestMethod::GET);
        $this->assertFalse($route->matches($request));
    }
}
