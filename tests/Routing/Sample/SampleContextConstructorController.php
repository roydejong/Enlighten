<?php

namespace Enlighten\Tests\Routing\Sample;

use Enlighten\Http\Request;

class SampleContextConstructorController
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function action()
    {
        return $this->request->getHostname();
    }
}