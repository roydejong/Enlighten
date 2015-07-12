<?php

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

        $context = new EnlightenContext();
        $context->setRequest($request);

        $this->assertEquals('retVal', $router->dispatch($route, $context));

        $this->expectOutputString('hello world');
    }

    /**
     * @runInSeparateProcess
     */
    public function testClosureContext()
    {
        $route = new Route('/hello/world', function () {
            $uri = $this->getRequest()->getRequestUri();
            echo $uri;
            return $uri;
        });

        $request = new Request();
        $request->setRequestUri('/hello/world');

        $router = new Router();
        $router->register($route);

        $context = new EnlightenContext();
        $context->setRequest($request);

        $this->assertEquals('/hello/world', $router->dispatch($route, $context));

        $this->expectOutputString('/hello/world');
    }
}