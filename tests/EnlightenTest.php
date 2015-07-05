<?php

use Enlighten\Enlighten;

class EnlightenTest extends PHPUnit_Framework_TestCase
{
    public function testInit()
    {
        $enlighten = new Enlighten();
    }

    /**
     * @runInSeparateProcess
     */
    public function testStart()
    {
        $enlighten = new Enlighten();
        $this->assertTrue($enlighten->start());
    }
}