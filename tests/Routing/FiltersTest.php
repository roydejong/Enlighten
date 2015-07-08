<?php

use Enlighten\Routing\Filters;

class FiltersTest extends PHPUnit_Framework_TestCase
{
    public function testRegistry()
    {
        $filters = new Filters();

        $filters->register('myEventType', function () {
            echo 'hello!';
        });

        $this->expectOutputString('hello!', 'Trigger() should result in a filter function being executed.');
        $this->assertTrue($filters->trigger('myEventType'), 'Trigger() should return true if an event was triggered');
        $this->assertFalse($filters->trigger('bogusEventType', 'Trigger() should returrn false if no event was triggered'));
    }
}