<?php

use Enlighten\Enlighten;
use Enlighten\Http\Request;

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
     * @depends testStart
     */
    public function testHeadRequest()
    {
        $enlighten = new Enlighten();

        $request = new Request();
        $request->setRequestUri('/');
        $request->setMethod('HEAD');

        $response = $enlighten->start();

        $this->assertEmpty($response->getBody());
    }
}