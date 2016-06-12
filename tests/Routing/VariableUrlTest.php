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

    public function testApplyUrlVariables()
    {
        $inputPattern = '/example/$myVar/bla/$secondary';
        $inputSet = ['myVar' => 'replaced', 'secondary' => 'anotherOne', 'extra' => 'butNotThis'];

        $output = VariableUrl::applyUrlVariables($inputPattern, $inputSet);

        $this->assertEquals('/example/replaced/bla/anotherOne', $output);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage does not contain requested URL variable: $missing
     */
    public function testApplyUrlVariablesThrowsExceptionForMissingVariables()
    {
        $inputPattern = '/example/$myVar/bla/$secondary/$missing';
        $inputSet = ['myVar' => 'replaced', 'secondary' => 'anotherOne', 'extra' => 'butNotThis'];

        VariableUrl::applyUrlVariables($inputPattern, $inputSet);
    }
}