<?php

use Enlighten\Enlighten;
use Enlighten\Http\Request;
use Enlighten\Http\RequestMethod;
use Enlighten\Http\ResponseCode;
use Enlighten\Routing\Route;
use Enlighten\Routing\Router;

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

        $route = new Route('/', function (Request $request) {
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

        $route = new Route('/', function (Request $request) {
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
}