<?php

use Enlighten\EnlightenContext;
use Enlighten\Http\Request;
use Enlighten\Http\Response;

class EnlightenContextTest extends PHPUnit_Framework_TestCase
{
    public function testGetSetRequest()
    {
        $c = new EnlightenContext();
        $r = new Request();

        $this->assertNull($c->getRequest(), 'Default empty');
        $this->assertEquals($c, $c->setRequest($r), 'Fluent API');
        $this->assertEquals($r, $c->getRequest());
    }

    public function testGetSetResponse()
    {
        $c = new EnlightenContext();
        $r = new Response();

        $this->assertNull($c->getResponse(), 'Default empty');
        $this->assertEquals($c, $c->setResponse($r), 'Fluent API');
        $this->assertEquals($r, $c->getResponse());
    }
}