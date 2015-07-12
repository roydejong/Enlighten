<?php

namespace Enlighten\Tests\Routing\Sample;

class SampleBadConstructorController
{
    public function __construct($ass)
    {
        // ...
    }

    public function action()
    {
        echo 'defaultAction';
        return 'defaultReturn';
    }
}