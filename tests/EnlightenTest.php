<?php

use Enlighten\Context;
use Enlighten\Enlighten;
use Enlighten\Http\Request;
use Enlighten\Http\RequestMethod;
use Enlighten\Http\Response;
use Enlighten\Http\ResponseCode;
use Enlighten\Routing\Route;
use Enlighten\Routing\Router;
use Symfony\Component\Config\Definition\Exception\Exception;

class EnlightenTest extends PHPUnit_Framework_TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testStart()
    {
        $enlighten = new Enlighten();
        $this->assertInstanceOf('Enlighten\Http\Response', $enlighten->start());
    }

    /**
     * @runInSeparateProcess
     */
    public function testApplicationRouting()
    {
        $enlighten = new Enlighten();

        $request = new Request();
        $request->setRequestUri('/');
        $request->setMethod(RequestMethod::GET);

        $route = new Route('/', function () {
            echo 'test output';
        });

        $router = new Router();
        $router->register($route);

        $enlighten->setRouter($router);
        $enlighten->setRequest($request);

        $response = $enlighten->start();

        $this->assertEquals(ResponseCode::HTTP_OK, $response->getResponseCode());
        $this->assertEquals('test output', $response->getBody());
        $this->expectOutputString('test output');
    }

    /**
     * @runInSeparateProcess
     */
    public function testOutputsReturnedResponses()
    {
        $enlighten = new Enlighten();

        $request = new Request();
        $request->setRequestUri('/');
        $request->setMethod(RequestMethod::GET);

        $route = new Route('/', function () {
            $customResponse = new Response();
            $customResponse->setResponseCode(ResponseCode::HTTP_IM_A_TEAPOT);
            $customResponse->setBody('bla');
            return $customResponse;
        });

        $router = new Router();
        $router->register($route);

        $enlighten->setRouter($router);
        $enlighten->setRequest($request);

        $response = $enlighten->start();

        $this->assertEquals(ResponseCode::HTTP_IM_A_TEAPOT, $response->getResponseCode());
        $this->assertEquals('bla', $response->getBody());
        $this->expectOutputString('bla');
    }

    /**
     * @runInSeparateProcess
     */
    public function testApplicationRouting404()
    {
        $enlighten = new Enlighten();

        $request = new Request();
        $request->setRequestUri('/wrong');
        $request->setMethod(RequestMethod::GET);

        $route = new Route('/', function (Request $request) {
            echo 'test output';
        });

        $router = new Router();
        $router->register($route);

        $enlighten->setRouter($router);
        $enlighten->setRequest($request);

        $response = $enlighten->start();

        $this->assertEquals(ResponseCode::HTTP_NOT_FOUND, $response->getResponseCode());

        // if no 404 handler is supplied, we should get a default message
        $this->assertContains('Page not found.', $response->getBody());
    }

    /**
     * @runInSeparateProcess
     */
    public function testRouteRegistration()
    {
        try {
            $router = new Router();

            $enlighten = new Enlighten();
            $enlighten->setRouter($router);

            $generatedRoute = $enlighten->route('/test/route.html', function () {
                echo 'test';
            });

            $sampleRequest = new Request();
            $sampleRequest->setRequestUri('/test/route.html');
            $sampleRequest->setMethod(RequestMethod::POST);

            $this->assertTrue($generatedRoute->matches($sampleRequest));

            $sampleRequest->setMethod(RequestMethod::HEAD);

            $this->assertTrue($generatedRoute->matches($sampleRequest));

            $sampleRequest->setMethod(RequestMethod::PATCH);

            $enlighten->setRequest($sampleRequest);
            $enlighten->start();

            $this->expectOutputString('test');
        } catch (PHPUnit_Framework_Exception $ex) {
            if ($ex->getMessage() == 'Segmentation fault (core dumped)') {
                $this->markTestSkipped('Segmentation fault occured in testRouteRegistration(), silly PHP 7');
            } else {
                throw $ex;
            }
        }
    }

    public function testRouteRegistrationGet()
    {
        $router = new Router();

        $enlighten = new Enlighten();
        $enlighten->setRouter($router);

        $generatedRoute = $enlighten->get('/test/route.html', function () {
            // ...
        });

        $sampleRequest = new Request();
        $sampleRequest->setRequestUri('/test/route.html');
        $sampleRequest->setMethod(RequestMethod::GET);

        $this->assertTrue($generatedRoute->matches($sampleRequest));

        $sampleRequest->setMethod(RequestMethod::OPTIONS);

        $this->assertFalse($generatedRoute->matches($sampleRequest));

        $sampleRequest->setMethod(RequestMethod::HEAD);

        $this->assertTrue($generatedRoute->matches($sampleRequest), 'HEAD acts as an alias for GET');
    }

    public function testRouteRegistrationPost()
    {
        $router = new Router();

        $enlighten = new Enlighten();
        $enlighten->setRouter($router);

        $generatedRoute = $enlighten->post('/test/route.html', function () {
            // ...
        });

        $sampleRequest = new Request();
        $sampleRequest->setRequestUri('/test/route.html');
        $sampleRequest->setMethod(RequestMethod::POST);

        $this->assertTrue($generatedRoute->matches($sampleRequest));

        $sampleRequest->setMethod(RequestMethod::GET);

        $this->assertFalse($generatedRoute->matches($sampleRequest));
    }

    public function testRouteRegistrationPut()
    {
        $router = new Router();

        $enlighten = new Enlighten();
        $enlighten->setRouter($router);

        $generatedRoute = $enlighten->put('/test/route.html', function () {
            // ...
        });

        $sampleRequest = new Request();
        $sampleRequest->setRequestUri('/test/route.html');
        $sampleRequest->setMethod(RequestMethod::PUT);

        $this->assertTrue($generatedRoute->matches($sampleRequest));

        $sampleRequest->setMethod(RequestMethod::POST);

        $this->assertFalse($generatedRoute->matches($sampleRequest));
    }

    public function testRouteRegistrationPatch()
    {
        $router = new Router();

        $enlighten = new Enlighten();
        $enlighten->setRouter($router);

        $generatedRoute = $enlighten->patch('/test/route.html', function () {
            // ...
        });

        $sampleRequest = new Request();
        $sampleRequest->setRequestUri('/test/route.html');
        $sampleRequest->setMethod(RequestMethod::PATCH);

        $this->assertTrue($generatedRoute->matches($sampleRequest));

        $sampleRequest->setMethod(RequestMethod::POST);

        $this->assertFalse($generatedRoute->matches($sampleRequest));
    }

    public function testRouteRegistrationDelete()
    {
        $router = new Router();

        $enlighten = new Enlighten();
        $enlighten->setRouter($router);

        $generatedRoute = $enlighten->delete('/test/route.html', function () {
            // ...
        });

        $sampleRequest = new Request();
        $sampleRequest->setRequestUri('/test/route.html');
        $sampleRequest->setMethod(RequestMethod::DELETE);

        $this->assertTrue($generatedRoute->matches($sampleRequest));

        $sampleRequest->setMethod(RequestMethod::HEAD);

        $this->assertFalse($generatedRoute->matches($sampleRequest));
    }

    public function testRouteRegistrationOptions()
    {
        $router = new Router();

        $enlighten = new Enlighten();
        $enlighten->setRouter($router);

        $generatedRoute = $enlighten->options('/test/route.html', function () {
            // ...
        });

        $sampleRequest = new Request();
        $sampleRequest->setRequestUri('/test/route.html');
        $sampleRequest->setMethod(RequestMethod::OPTIONS);

        $this->assertTrue($generatedRoute->matches($sampleRequest));

        $sampleRequest->setMethod(RequestMethod::DELETE);

        $this->assertFalse($generatedRoute->matches($sampleRequest));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRedirectRegistration()
    {
        $request = new Request();
        $request->setRequestUri('/test/route.html');

        $enlighten = new Enlighten();
        $enlighten->redirect('/test/route.html', 'http://target.com', false);
        $enlighten->setRequest($request);
        $response = $enlighten->start();

        $this->assertEquals(ResponseCode::HTTP_FOUND, $response->getResponseCode());
        $this->assertEquals('http://target.com', $response->getHeader('Location'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testBeforeFilter()
    {
        $route = new Route('/', function () {
            echo 'during';
        });

        $router = new Router();
        $router->register($route);

        $request = new Request();
        $request->setRequestUri('/');

        $enlighten = new Enlighten();
        $enlighten->setRouter($router);
        $enlighten->setRequest($request);
        $enlighten->before(function () {
            echo 'before';
        });
        $enlighten->start();

        $this->expectOutputString('beforeduring');
    }

    /**
     * @runInSeparateProcess
     */
    public function testAfterFilter()
    {
        $route = new Route('/', function () {
            echo 'during';
        });

        $router = new Router();
        $router->register($route);

        $request = new Request();
        $request->setRequestUri('/');

        $enlighten = new Enlighten();
        $enlighten->setRouter($router);
        $enlighten->setRequest($request);
        $enlighten->after(function () {
            echo 'after';
        });
        $enlighten->start();

        $this->expectOutputString('duringafter');
    }

    /**
     * @runInSeparateProcess
     */
    public function testExceptionFilter()
    {
        $route = new Route('/', function () {
            throw new Exception('Testex');
        });

        $router = new Router();
        $router->register($route);

        $request = new Request();
        $request->setRequestUri('/');

        $enlighten = new Enlighten();
        $enlighten->setRouter($router);
        $enlighten->setRequest($request);
        $enlighten->onException(function (\Exception $ex) {
            echo $ex->getMessage();
        });
        $response = $enlighten->start();

        $this->expectOutputString('Testex', 'An error handler with output is registered; no default message should be returned and only our output should be visible.');
        $this->assertEquals(ResponseCode::HTTP_INTERNAL_SERVER_ERROR, $response->getResponseCode());
    }

    /**
     * @runInSeparateProcess
     */
    public function testExceptionFilterOutputViaResponseBody()
    {
        $route = new Route('/bla', function () {
            throw new Exception('Testex');
        });

        $router = new Router();
        $router->register($route);

        $request = new Request();
        $request->setRequestUri('/bla');

        $enlighten = new Enlighten();
        $enlighten->setRouter($router);
        $enlighten->setRequest($request);
        $enlighten->onException(function (Response $response) {
            $response->setBody('my output');
        });
        $enlighten->start();

        $this->expectOutputString('my output');
    }

    /**
     * @runInSeparateProcess
     */
    public function testExceptionFilterUncaught()
    {
        $route = new Route('/', function () {
            throw new Exception('Testex');
        });

        $router = new Router();
        $router->register($route);

        $request = new Request();
        $request->setRequestUri('/');

        $enlighten = new Enlighten();
        $enlighten->setRouter($router);
        $enlighten->setRequest($request);

        $caughtEx = false;
        $resp = null;

        try {
            $resp = $enlighten->start();
        } catch (\Exception $ex) {
            $this->assertEquals('Testex', $ex->getMessage());
            $caughtEx = true;
        }

        $this->assertTrue($caughtEx);
        $this->assertContains('Sorry, something went wrong.', ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     */
    public function testNotFoundFilter()
    {
        $request = new Request();
        $request->setRequestUri('/bla');

        $enlighten = new Enlighten();
        $enlighten->setRequest($request);
        $enlighten->notFound(function (Request $request) {
            echo sprintf("Sorry, but there is no %s here", $request->getRequestUri());
        });
        $enlighten->start();

        $this->expectOutputString('Sorry, but there is no /bla here');
    }

    /**
     * @runInSeparateProcess
     */
    public function testNotFoundFilterOutputViaResponseBody()
    {
        $request = new Request();
        $request->setRequestUri('/bla');

        $enlighten = new Enlighten();
        $enlighten->setRequest($request);
        $enlighten->notFound(function (Response $response) {
            $response->setBody('my output');
        });
        $enlighten->start();

        $this->expectOutputString('my output');
    }

    public function testSetSubdirectory()
    {
        $router = new Router();

        $app = new Enlighten();
        $app->setRouter($router);

        $this->assertEquals($app, $app->setSubdirectory('/dir/bla'), 'Fluent API return');
        $this->assertEquals('/dir/bla', $router->getSubdirectory());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetOverrideResponse()
    {
        $request = new Request();
        $request->setRequestUri('/bla');

        $enlighten = new Enlighten();
        $enlighten->setRequest($request);
        $enlighten->notFound(function (Response $response) use ($enlighten) {
            $responseOverride = new Response();
            $responseOverride->setResponseCode(ResponseCode::HTTP_IM_A_TEAPOT);
            $responseOverride->setBody('i am a teapot');

            $enlighten->setResponse($responseOverride);
        });
        $response = $enlighten->start();

        $this->assertEquals(ResponseCode::HTTP_IM_A_TEAPOT, $response->getResponseCode());
        $this->expectOutputString('i am a teapot');
    }

    /**
     * @runInSeparateProcess
     */
    public function testFirstRunPage()
    {
        $request = new Request();
        $request->setRequestUri('/bla');

        $enlighten = new Enlighten();
        $enlighten->setRequest($request);
        $response = $enlighten->start();

        $this->assertContains('Welcome to Enlighten', $response->getBody());
    }

    /**
     * @runInSeparateProcess
     */
    public function testBeforeFilterCanPreventContinue()
    {
        $app = new Enlighten();

        $app->before(function (Response $r) {
            // Oh no you don't!
            $r->setBody('intercept!');
            return false;
        });

        $app->get('/', function () {
            echo 'hi!';
        });

        $request = new Request();
        $request->setRequestUri('/');

        $app->setRequest($request);
        $app->start();

        $this->expectOutputString('intercept!', 'Filter function should interrupt execution');
    }

    /**
     * @runInSeparateProcess
     */
    public function testRouteBeforeFilterCanPreventContinueButOutputManipulationStillWorks()
    {
        $app = new Enlighten();

        $app->get('/', function () {
            echo 'hi!';
        })->before(function (Response $r) {
            // Oh no you don't!
            $r->setBody('intercept!');
            return false;
        });

        $request = new Request();
        $request->setRequestUri('/');

        $app->setRequest($request);
        $app->start();

        $this->expectOutputString('intercept!', 'Filter function should interrupt execution');
    }

    /**
     * @runInSeparateProcess 
     */
    public function testDefaultOptionsResponse()
    {
        $app = new Enlighten();

        $app->get('/sample', function () {
            echo 'hi!!!';
        });

        $optionsRequest = new Request();
        $optionsRequest->setRequestUri('/sample');
        $optionsRequest->setMethod(RequestMethod::OPTIONS);
        
        $app->setRequest($optionsRequest);
        $response = $app->start();

        $this->assertEmpty($response->getBody());
        $this->assertEquals(ResponseCode::HTTP_OK, $response->getResponseCode());
        $this->assertEquals('OPTIONS,GET,HEAD', $response->getHeader('Allow'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testCustomOptionsResponse()
    {
        $router = new Router();

        $unrestrictedRoute = new Route('/sample', function (Request $request) {
            echo 'hello ' . $request->getMethod() . '!';
        });

        $router->register($unrestrictedRoute);

        $app = new Enlighten();
        $app->setRouter($router);

        $optionsRequest = new Request();
        $optionsRequest->setRequestUri('/sample');
        $optionsRequest->setMethod(RequestMethod::OPTIONS);

        $app->setRequest($optionsRequest);
        $response = $app->start();

        $this->assertEquals('hello OPTIONS!', $response->getBody());
        $this->assertEquals(ResponseCode::HTTP_OK, $response->getResponseCode());

    }

    /**
     * @runInSeparateProcess
     */
    public function testNotAllowedResponse()
    {
        $app = new Enlighten();

        $app->get('/sample', function () {
            echo 'hi!!!';
        });

        $optionsRequest = new Request();
        $optionsRequest->setRequestUri('/sample');
        $optionsRequest->setMethod(RequestMethod::POST);

        $app->setRequest($optionsRequest);
        $response = $app->start();

        $this->assertEquals(ResponseCode::HTTP_METHOD_NOT_ALLOWED, $response->getResponseCode());
    }
}