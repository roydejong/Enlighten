<?php

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
        $router->clear();

        $this->assertEquals('retVal', $router->dispatch($route, $request));

        $this->expectOutputString('hello world');
    }
}