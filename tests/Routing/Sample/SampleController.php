<?php

namespace Enlighten\Tests\Routing\Sample;

class SampleController
{
    public function action()
    {
        echo 'defaultAction';
        return 'defaultReturn';
    }

    public function myAction()
    {
        echo 'myAction';
        return 'myReturn';
    }
}