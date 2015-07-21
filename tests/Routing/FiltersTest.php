<?php

use Enlighten\Context;
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
        $this->assertFalse($filters->trigger('bogusEventType'), 'Trigger() should returrn false if no event was triggered');
    }

    public function testFiltersWithContext()
    {
        $filters = new Filters();

        $filters->register('myEventType', function (Filters $contextFilters) use ($filters) {
           $this->assertEquals($filters, $contextFilters, 'Context should pass data to our filter function');
        });

        $context = new Context();
        $context->registerInstance($filters);

        $filters->trigger('myEventType', $context);
    }
}