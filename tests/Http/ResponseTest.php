<?php

use Enlighten\Http\Response;
use Enlighten\Http\ResponseCode;

class ResponseTest extends PHPUnit_Framework_TestCase
{
    public function testResponseCodeGetSet()
    {
        $response = new Response();
        $this->assertEquals(ResponseCode::HTTP_OK, $response->getResponseCode(), 'Default response code should be 200 (OK)');
        $response->setResponseCode(ResponseCode::HTTP_PAYMENT_REQUIRED);
        $this->assertEquals(ResponseCode::HTTP_PAYMENT_REQUIRED, $response->getResponseCode());
    }

    /**
     * @expectedException InvalidArgumentException
     * @depends testResponseCodeGetSet
     */
    public function testResponseCodeSetInvalid()
    {
        $response = new Response();
        $testCode = 666;
        $this->assertFalse(ResponseCode::isValid($testCode));
        $response->setResponseCode($testCode); // should raise exception
    }

    public function testHeaderGetSet()
    {
        $response = new Response();
        $this->assertNull($response->getHeader('Test'));
        $response->setHeader('Test', 'Value');
        $this->assertEquals('Value', $response->getHeader('Test'));
    }

    public function testBodyGetSet()
    {
        $response = new Response();
        $this->assertEquals('', $response->getBody());
        $response->setBody('Test');
        $this->assertEquals('Test', $response->getBody());
        $response->appendBody('Test');
        $this->assertEquals('TestTest', $response->getBody());
        $response->setBody('CleanTest');
        $this->assertEquals('CleanTest', $response->getBody());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendHeaders()
    {
        if (!function_exists('xdebug_get_headers')) {
            $this->markTestSkipped('xdebug is not installed');
        }

        $response = new Response();

        $response->setResponseCode(ResponseCode::HTTP_BAD_GATEWAY);
        $response->setHeader('X-Check-Out', 'http://www.google.com');

        $this->expectOutputString('');
        $response->send();

        $headers = xdebug_get_headers();

        $this->assertContains('X-Check-Out: http://www.google.com', $headers, '', true);
        $this->assertEquals($response->getResponseCode(), http_response_code());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendBody()
    {
        $response = new Response();
        $response->setBody('test!');
        $this->expectOutputString('test!');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     */
    public function testBodylessResponse()
    {
        $response = new Response();
        $response->setResponseCode(ResponseCode::HTTP_NO_CONTENT);
        $response->setBody('test!');
        $this->expectOutputString('');
        $response->send();
    }

    public function testRedirect()
    {
        $response = new Response();
        $response->doRedirect('/teapot', true);

        $this->assertEquals(ResponseCode::HTTP_MOVED_PERMANENTLY, $response->getResponseCode());
        $this->assertEquals('/teapot', $response->getHeader('Location'));
    }

    public function testTemporaryRedirect()
    {
        $response = new Response();
        $response->doRedirect('/kettle');

        $this->assertEquals(ResponseCode::HTTP_TEMPORARY_REDIRECT, $response->getResponseCode());
        $this->assertEquals('/kettle', $response->getHeader('Location'));
    }
}