<?php

use Enlighten\Context;
use Enlighten\EnlightenContext;
use Enlighten\Http\Request;
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
}