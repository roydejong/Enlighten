<?php

use Enlighten\Http\ResponseCode;

class ResponseCodeTest extends PHPUnit_Framework_TestCase
{
    public function testIsError()
    {
        $this->assertTrue(ResponseCode::isError(ResponseCode::HTTP_NOT_FOUND));
        $this->assertTrue(ResponseCode::isError(ResponseCode::HTTP_INTERNAL_SERVER_ERROR));
        $this->assertTrue(ResponseCode::isError(ResponseCode::HTTP_BAD_GATEWAY));
        $this->assertTrue(ResponseCode::isError(ResponseCode::HTTP_BAD_REQUEST));

        $this->assertFalse(ResponseCode::isError(ResponseCode::HTTP_OK));
        $this->assertFalse(ResponseCode::isError(ResponseCode::HTTP_CONTINUE));
        $this->assertFalse(ResponseCode::isError(ResponseCode::HTTP_IM_A_TEAPOT));

        $this->assertFalse(ResponseCode::isError(''));
        $this->assertFalse(ResponseCode::isError('a'));

        $this->assertTrue(ResponseCode::isError('999'));
    }

    public function testCanHaveBody()
    {
        $this->assertTrue(ResponseCode::canHaveBody(ResponseCode::HTTP_OK));
        $this->assertFalse(ResponseCode::canHaveBody(ResponseCode::HTTP_CONTINUE));
        $this->assertFalse(ResponseCode::canHaveBody(ResponseCode::HTTP_SWITCHING_PROTOCOLS));
        $this->assertFalse(ResponseCode::canHaveBody(ResponseCode::HTTP_NO_CONTENT));
        $this->assertFalse(ResponseCode::canHaveBody(ResponseCode::HTTP_NOT_MODIFIED));
    }

    public function testIsValid()
    {
        $this->assertTrue(ResponseCode::isValid(200));
        $this->assertTrue(ResponseCode::isValid(100));
        $this->assertTrue(ResponseCode::isValid(404));
        $this->assertTrue(ResponseCode::isValid(ResponseCode::HTTP_IM_A_TEAPOT));

        $this->assertFalse(ResponseCode::isValid(''));
        $this->assertFalse(ResponseCode::isValid(0));
        $this->assertFalse(ResponseCode::isValid(999));
        $this->assertFalse(ResponseCode::isValid('a'));
    }
}