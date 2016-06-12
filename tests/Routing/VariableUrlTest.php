<?php

use Enlighten\Http\Request;
use Enlighten\Routing\Route;
use Enlighten\Routing\VariableUrl;

class VariableUrlTest extends PHPUnit_Framework_TestCase
{
    public function testVariableMapping()
    {
        $routePatten = '/view/user/$id/do/$action';
        $requestUri = '/view/user/5/do/teststr_123.html';

        $route = new Route($routePatten, function () {
            // ...
        });

        $request = new Request();
        $request->setRequestUri($requestUri);

        $this->assertTrue($route->matches($request));

        $this->assertEquals(['id' => '5', 'action' => 'teststr_123.html'],
            VariableUrl::extractUrlVariables($requestUri, $routePatten));
    }
}