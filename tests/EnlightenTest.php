<?php

use Enlighten\Enlighten;
use Enlighten\Http\Request;
use Enlighten\Http\RequestMethod;
use Enlighten\Http\ResponseCode;
use Enlighten\Routing\Route;
use Enlighten\Routing\Router;
use Symfony\Component\Config\Definition\Exception\Exception;

class EnlightenTest extends PHPUnit_Framework_TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testStart()
    {
        $enlighten = new Enlighten();
        $this->assertInstanceOf('Enlighten\Http\Response', $enlighten->start());
    }

    /**
     * @runInSeparateProcess
     */
    public function testApplicationRouting()
    {
        $enlighten = new Enlighten();

        $request = new Request();
        $request->setRequestUri('/');
        $request->setMethod(RequestMethod::GET);

        $route = new Route('/', function () {
            echo 'test output';
        });

        $router = new Router();
        $router->register($route);

        $enlighten->setRouter($router);
        $enlighten->setRequest($request);

        $response = $enlighten->start();

        $this->assertEquals(ResponseCode::HTTP_OK, $response->getResponseCode());
        $this->assertEquals('test output', $response->getBody());
        $this->expectOutputString('test output');
    }

    /**
     * @runInSeparateProcess
     */
    public function testApplicationRouting404()
    {
        $enlighten = new Enlighten();

        $request = new Request();
        $request->setRequestUri('/wrong');
        $request->setMethod(RequestMethod::GET);

        $route = new Route('/', function (Request $request) {
            echo 'test output';
        });

        $router = new Router();
        $router->register($route);

        $enlighten->setRouter($router);
        $enlighten->setRequest($request);

        $response = $enlighten->start();

        $this->assertEquals(ResponseCode::HTTP_NOT_FOUND, $response->getResponseCode());
        $this->assertEquals('', $response->getBody());
        $this->expectOutputString('');
    }

    /**
     * @runInSeparateProcess
     */
    public function testHeadersOnlyRequest()
    {
        $enlighten = new Enlighten();

        $request = new Request();
        $request->setRequestUri('/');
        $request->setMethod(RequestMethod::HEAD);

        $route = new Route('/', function () {
            echo 'test output';
        });

        $router = new Router();
        $router->register($route);

        $enlighten->setRouter($router);
        $enlighten->setRequest($request);

        $response = $enlighten->start();

        $this->assertEquals(ResponseCode::HTTP_OK, $response->getResponseCode());
        $this->assertEquals('', $response->getBody());
        $this->expectOutputString('');
    }

    /**
     * @runInSeparateProcess
     */
    public function testRouteRegistration()
    {
        $router = new Router();

        $enlighten = new Enlighten();
        $enlighten->setRouter($router);

        $generatedRoute = $enlighten->route('/test/route.html', function () {
            echo 'test';
        });

        $sampleRequest = new Request();
        $sampleRequest->setRequestUri('/test/route.html');
        $sampleRequest->setMethod(RequestMethod::POST);

        $this->assertTrue($generatedRoute->matches($sampleRequest));

        $sampleRequest->setMethod(RequestMethod::HEAD);

        $this->assertTrue($generatedRoute->matches($sampleRequest));

        $sampleRequest->setMethod(RequestMethod::PATCH);

        $enlighten->setRequest($sampleRequest);
        $enlighten->start();

        $this->expectOutputString('test');
    }

    public function testRouteRegistrationGet()
    {
        $router = new Router();

        $enlighten = new Enlighten();
        $enlighten->setRouter($router);

        $generatedRoute = $enlighten->get('/test/route.html', function () {
            // ...
        });

        $sampleRequest = new Request();
        $sampleRequest->setRequestUri('/test/route.html');
        $sampleRequest->setMethod(RequestMethod::GET);

        $this->assertTrue($generatedRoute->matches($sampleRequest));

        $sampleRequest->setMethod(RequestMethod::OPTIONS);

        $this->assertFalse($generatedRoute->matches($sampleRequest));
    }

    public function testRouteRegistrationPost()
    {
        $router = new Router();

        $enlighten = new Enlighten();
        $enlighten->setRouter($router);

        $generatedRoute = $enlighten->post('/test/route.html', function () {
            // ...
        });

        $sampleRequest = new Request();
        $sampleRequest->setRequestUri('/test/route.html');
        $sampleRequest->setMethod(RequestMethod::POST);

        $this->assertTrue($generatedRoute->matches($sampleRequest));

        $sampleRequest->setMethod(RequestMethod::GET);

        $this->assertFalse($generatedRoute->matches($sampleRequest));
    }

    public function testRouteRegistrationPut()
    {
        $router = new Router();

        $enlighten = new Enlighten();
        $enlighten->setRouter($router);

        $generatedRoute = $enlighten->put('/test/route.html', function () {
            // ...
        });

        $sampleRequest = new Request();
        $sampleRequest->setRequestUri('/test/route.html');
        $sampleRequest->setMethod(RequestMethod::PUT);

        $this->assertTrue($generatedRoute->matches($sampleRequest));

        $sampleRequest->setMethod(RequestMethod::POST);

        $this->assertFalse($generatedRoute->matches($sampleRequest));
    }

    public function testRouteRegistrationPatch()
    {
        $router = new Router();

        $enlighten = new Enlighten();
        $enlighten->setRouter($router);

        $generatedRoute = $enlighten->patch('/test/route.html', function () {
            // ...
        });

        $sampleRequest = new Request();
        $sampleRequest->setRequestUri('/test/route.html');
        $sampleRequest->setMethod(RequestMethod::PATCH);

        $this->assertTrue($generatedRoute->matches($sampleRequest));

        $sampleRequest->setMethod(RequestMethod::POST);

        $this->assertFalse($generatedRoute->matches($sampleRequest));
    }

    public function testRouteRegistrationHead()
    {
        $router = new Router();

        $enlighten = new Enlighten();
        $enlighten->setRouter($router);

        $generatedRoute = $enlighten->head('/test/route.html', function () {
            // ...
        });

        $sampleRequest = new Request();
        $sampleRequest->setRequestUri('/test/route.html');
        $sampleRequest->setMethod(RequestMethod::HEAD);

        $this->assertTrue($generatedRoute->matches($sampleRequest));

        $sampleRequest->setMethod(RequestMethod::PUT);

        $this->assertFalse($generatedRoute->matches($sampleRequest));
    }

    public function testRouteRegistrationDelete()
    {
        $router = new Router();

        $enlighten = new Enlighten();
        $enlighten->setRouter($router);

        $generatedRoute = $enlighten->delete('/test/route.html', function () {
            // ...
        });

        $sampleRequest = new Request();
        $sampleRequest->setRequestUri('/test/route.html');
        $sampleRequest->setMethod(RequestMethod::DELETE);

        $this->assertTrue($generatedRoute->matches($sampleRequest));

        $sampleRequest->setMethod(RequestMethod::HEAD);

        $this->assertFalse($generatedRoute->matches($sampleRequest));
    }

    public function testRouteRegistrationOptions()
    {
        $router = new Router();

        $enlighten = new Enlighten();
        $enlighten->setRouter($router);

        $generatedRoute = $enlighten->options('/test/route.html', function () {
            // ...
        });

        $sampleRequest = new Request();
        $sampleRequest->setRequestUri('/test/route.html');
        $sampleRequest->setMethod(RequestMethod::OPTIONS);

        $this->assertTrue($generatedRoute->matches($sampleRequest));

        $sampleRequest->setMethod(RequestMethod::DELETE);

        $this->assertFalse($generatedRoute->matches($sampleRequest));
    }

    /**
     * @runInSeparateProcess
     */
    public function testBeforeFilter()
    {
        $route = new Route('/', function () {
            echo 'during';
        });

        $router = new Router();
        $router->register($route);

        $request = new Request();
        $request->setRequestUri('/');

        $enlighten = new Enlighten();
        $enlighten->setRouter($router);
        $enlighten->setRequest($request);
        $enlighten->before(function () {
            echo 'before';
        });
        $enlighten->start();

        $this->expectOutputString('beforeduring');
    }

    /**
     * @runInSeparateProcess
     */
    public function testAfterFilter()
    {
        $route = new Route('/', function () {
            echo 'during';
        });

        $router = new Router();
        $router->register($route);

        $request = new Request();
        $request->setRequestUri('/');

        $enlighten = new Enlighten();
        $enlighten->setRouter($router);
        $enlighten->setRequest($request);
        $enlighten->after(function () {
            echo 'after';
        });
        $enlighten->start();

        $this->expectOutputString('duringafter');
    }

    /**
     * @runInSeparateProcess
     */
    public function testExceptionFilter()
    {
        $route = new Route('/', function () {
            throw new Exception('Testex');
        });

        $router = new Router();
        $router->register($route);

        $request = new Request();
        $request->setRequestUri('/');

        $enlighten = new Enlighten();
        $enlighten->setRouter($router);
        $enlighten->setRequest($request);
        $enlighten->onException(function (\Exception $ex) {
            echo $ex->getMessage();
        });
        $enlighten->start();

        $this->expectOutputString('Testex');
    }

    /**
     * @runInSeparateProcess
     * @expectedException \Exception
     */
    public function testExceptionFilterUncaught()
    {
        $route = new Route('/', function () {
            throw new Exception('Testex');
        });

        $router = new Router();
        $router->register($route);

        $request = new Request();
        $request->setRequestUri('/');

        $enlighten = new Enlighten();
        $enlighten->setRouter($router);
        $enlighten->setRequest($request);
        $enlighten->start();

        $this->expectOutputString('Testex');
    }
}