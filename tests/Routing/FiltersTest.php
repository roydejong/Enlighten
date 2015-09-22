<?php

use Enlighten\Context;
use Enlighten\Routing\Filters;

class FiltersTest extends PHPUnit_Framework_TestCase
{
    public function testTrigger()
    {
        $filters = new Filters();

        $filters->register('myEventType', function () {
            echo 'hello!';
        });

        $this->expectOutputString('hello!', 'Trigger() should result in a filter function being executed.');

        $filters->trigger('myEventType');
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

    public function testContinueIsTrueByWhenExplicitlyReturned()
    {
        $filters = new Filters();

        $filters->register('myEventType', function () {
            return true;
        });

        $this->assertTrue($filters->trigger('myEventType'), 'Continue should be true when explicitly returned');
    }

    public function testContinueIsTrueOnNonBooleanReturned()
    {
        $filters = new Filters();

        $filters->register('myEventType', function () {
            return null;
        });

        $this->assertTrue($filters->trigger('myEventType'), 'Continue should be true as long as FALSE is not explicitly returned');
    }

    public function testContinueIsFalseOnFalseReturned()
    {
        $filters = new Filters();

        $filters->register('myEventType', function () {
            return false;
        });

        $this->assertFalse($filters->trigger('myEventType'), 'Continue should be false when FALSE is explicitly returned');
    }

    public function testContinueFalseShouldBreakFilterChain()
    {
        $filters = new Filters();

        $filters->register('myEventType', function () {
            // First filter function returns FALSE and should break the chain of filters
            return false;
        });

        $filters->register('myEventType', function () {
            // Second filter function should never even be called.
            $this->fail();
            return false;
        });

        $this->assertFalse($filters->trigger('myEventType'), 'Continue should break filter function execution when FALSE is explicitly returned');
    }

    public function testContinueIsTrueByDefault()
    {
        $filters = new Filters();
        $this->assertTrue($filters->trigger('myEventType'), 'Continue should be true if no filters are triggered');
    }
}