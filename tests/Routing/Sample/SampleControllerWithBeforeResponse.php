<?php

namespace Enlighten\Tests\Routing\Sample;

use Enlighten\Http\Response;
use Enlighten\Routing\RoutingException;

class SampleControllerWithBeforeResponse
{
    public function action()
    {
        echo 'defaultAction';
        return 'defaultReturn';
    }

    public function before()
    {
        $r = new Response();
        $r->setResponseCode(500);
        $r->setBody('_responseFromBefore_');
        return $r;
    }

    public function myAction()
    {
        throw new RoutingException('This should never happen: before() returning a Response should prevent primary action');
    }
}