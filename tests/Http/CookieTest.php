<?php

use Enlighten\Http\Cookie;

class CookieTest extends PHPUnit_Framework_TestCase
{
    public function testGetSetName()
    {
        $cookie = new Cookie();

        $this->assertEquals('', $cookie->getName(), 'Default is empty');
        $this->assertEquals($cookie, $cookie->setName('Hello'), 'Fluent API');
        $this->assertEquals('Hello', $cookie->getName());
    }

    public function testGetSetValue()
    {
        $cookie = new Cookie();

        $this->assertEquals('', $cookie->getValue(), 'Default is empty');
        $this->assertEquals($cookie, $cookie->setValue('Hello'), 'Fluent API');
        $this->assertEquals('Hello', $cookie->getValue());
    }

    public function testGetSetExpire()
    {
        $cookie = new Cookie();

        $now = new DateTime();

        $this->assertEquals(null, $cookie->getExpire(), 'Default is null');
        $this->assertEquals($cookie, $cookie->setExpire($now), 'Fluent API');
        $this->assertEquals($now, $cookie->getExpire());
        $this->assertEquals($now->getTimestamp(), $cookie->getExpireTimestamp());
    }

    public function testGetSetExpireTimestamp()
    {
        $cookie = new Cookie();

        $now = time();

        $nowDt = new DateTime();
        $nowDt->setTimestamp($now);

        $this->assertEquals(null, $cookie->getExpire(), 'Default is null');
        $this->assertEquals($cookie, $cookie->setExpireTimestamp($now), 'Fluent API');
        $this->assertEquals($now, $cookie->getExpireTimestamp());
        $this->assertEquals($nowDt->getTimestamp(), $cookie->getExpire()->getTimestamp());
    }

    public function testGetSetExpireSession()
    {
        $cookie = new Cookie();

        $this->assertEquals(null, $cookie->getExpire(), 'Default is null');
        $this->assertEquals($cookie, $cookie->setExpireOnSession(), 'Fluent API');
        $this->assertEquals(0, $cookie->getExpireTimestamp());
        $this->assertEquals(null, $cookie->getExpire());
    }

    public function testGetSetPath()
    {
        $cookie = new Cookie();

        $this->assertEquals('', $cookie->getPath(), 'Default is empty');
        $this->assertEquals($cookie, $cookie->setPath('Hello'), 'Fluent API');
        $this->assertEquals('Hello', $cookie->getPath());
    }

    public function testGetSetDomain()
    {
        $cookie = new Cookie();

        $this->assertEquals('', $cookie->getDomain(), 'Default is empty');
        $this->assertEquals($cookie, $cookie->setDomain('Hello'), 'Fluent API');
        $this->assertEquals('Hello', $cookie->getDomain());
    }

    public function testGetSetSecure()
    {
        $cookie = new Cookie();

        $this->assertEquals(false, $cookie->isSecure(), 'Default is false');
        $this->assertEquals($cookie, $cookie->setSecure(true), 'Fluent API');
        $this->assertEquals(true, $cookie->isSecure());
    }

    public function testGetSetHttpOnly()
    {
        $cookie = new Cookie();

        $this->assertEquals(false, $cookie->isHttpOnly(), 'Default is false');
        $this->assertEquals($cookie, $cookie->setHttpOnly(true), 'Fluent API');
        $this->assertEquals(true, $cookie->isHttpOnly());
    }
}