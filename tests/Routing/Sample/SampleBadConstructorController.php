<?php

namespace Enlighten\Tests\Routing\Sample;

use Enlighten\Http\Request;

class SampleBadConstructorController extends SampleContextConstructorController
{
    public function __construct(Request $request, \Exception $badArg)
    {
        parent::__construct($request);
    }
}