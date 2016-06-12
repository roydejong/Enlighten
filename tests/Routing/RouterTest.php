<?php

use Enlighten\Context;
use Enlighten\Http\Request;
use Enlighten\Http\Response;
use Enlighten\Http\ResponseCode;
use Enlighten\Routing\Route;
use Enlighten\Routing\Router;

class RouterTest extends PHPUnit_Framework_TestCase
{
    public function testBlankRouter()
    {
        $request = new Request();
        $request->setRequestUri('/');

        $router = new Router();
        $this->assertNull($router->route($request));
        $this->assertTrue($router->isEmpty());
    }

    public function testSimpleRouteMatch()
    {
        $route = new Route('/', function () {
            // ...
        });

        $request = new Request();
        $request->setRequestUri('/');

        $router = new Router();
        $router->register($route);

        $this->assertFalse($router->isEmpty());
        $this->assertEquals($route, $router->route($request));
    }

    public function testRouterClear()
    {
        $route = new Route('/', function () {
            // ...
        });

        $request = new Request();
        $request->setRequestUri('/');

        $router = new Router();
        $router->register($route);

        $this->assertNotNull($router->route($request));

        $router->clear();

        $this->assertNull($router->route($request));
    }

    /**
     * @runInSeparateProcess
     */
    public function testDispatch()
    {
        $route = new Route('/', function () {
            echo 'hello world';
            return 'retVal';
        });

        $request = new Request();
        $request->setRequestUri('/');

        $router = new Router();
        $router->register($route);

        $context = new Context();
        $context->registerInstance($request);

        $this->assertEquals('retVal', $router->dispatch($route, $request, $context));

        $this->expectOutputString('hello world');
    }

    /**
     * @runInSeparateProcess
     */
    public function testClosureContext()
    {
        $route = new Route('/hello/world', function (Request $request) {
            $uri = $request->getRequestUri();
            echo $uri;
            return $uri;
        });

        $request = new Request();
        $request->setRequestUri('/hello/world');

        $context = new Context();
        $context->registerInstance($request);

        $router = new Router();
        $router->setContext($context);
        $router->register($route);

        $this->assertEquals('/hello/world', $router->dispatch($route, $request));

        $this->expectOutputString('/hello/world');
    }

    public function testGetSetSubdirectory()
    {
        $router = new Router();

        $route = new Route('/bla.html', null);
        $router->register($route);

        $this->assertNull($router->getSubdirectory(), 'Default should be null');
        $this->assertEquals($router, $router->setSubdirectory('/my/dir'), 'Fluent API return');
        $this->assertEquals('/my/dir', $router->getSubdirectory());
        $this->assertEquals($router->getSubdirectory(), '/my/dir', 'Routes should inherit subdirectory setting');
    }

    public function testGetSetContext()
    {
        $router = new Router();

        $context = new Context();

        $this->assertNull($router->getContext(), 'Default should be null');
        $this->assertEquals($router, $router->setContext($context), 'Fluent API return');
        $this->assertEquals($context, $router->getContext());
    }

    public function testPathVariablesMapping()
    {
        $route = new Route('/product/id/$id', function (Request $request, $id = null) {
            $this->assertEquals('test', $id);
        });

        $request = new Request();
        $request->setRequestUri('/product/id/test');

        $context = new Context();
        $context->registerInstance($request);

        $router = new Router();
        $router->setContext($context);
        $router->dispatch($route, $request);
    }

    public function testPathVariablesMappingWithAutoContext()
    {
        $route = new Route('/product/id/$id', function (Request $request, $id = null) {
            $this->assertEquals('test', $id);
        });

        $request = new Request();
        $request->setRequestUri('/product/id/test');

        $router = new Router();
        $router->dispatch($route, $request);
    }

    public function testCreateRedirect()
    {
        // Prepare: Prepare environment to capture response
        $response = new Response();

        $request = new Request();
        $request->setRequestUri('/redirect/bla');

        $context = new Context();
        $context->registerInstance($response);
        $context->registerInstance($request);

        $router = new Router();
        $router->setContext($context);

        $route = $router->createRedirect('/redirect/$testVar', '/target/$testVar', true);
        $this->assertEquals('/redirect/$testVar', $route->getPattern());

        $routeResult = $router->route($request);
        $this->assertEquals($route, $routeResult);

        $router->dispatch($routeResult, $request);
        $this->assertEquals(ResponseCode::HTTP_MOVED_PERMANENTLY, $response->getResponseCode());
        $this->assertEquals('/target/bla', $response->getHeader('Location'));
    }
}