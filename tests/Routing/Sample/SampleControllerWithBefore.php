<?php

namespace Enlighten\Tests\Routing\Sample;

class SampleControllerWithBefore
{
    public function action()
    {
        echo 'defaultAction';
        return 'defaultReturn';
    }

    public function before()
    {
        echo 'before';
    }

    public function myAction()
    {
        echo 'myAction';
        return 'myReturn';
    }
}