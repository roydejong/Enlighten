<?php

use Enlighten\Http\Request;
use Enlighten\Http\RequestMethod;

class RequestTest extends PHPUnit_Framework_TestCase
{
    public function testRequestMethodGetSet()
    {
        $request = new Request();
        $this->assertEquals(RequestMethod::GET, $request->getMethod(), 'Default should be GET');
        $request->setMethod(RequestMethod::PATCH);
        $this->assertEquals(RequestMethod::PATCH, $request->getMethod());
    }

    public function testRequestMethodPost()
    {
        $request = new Request();
        $request->setMethod(RequestMethod::POST);
        $this->assertTrue($request->isPost());
        $this->assertFalse($request->isGet());
    }

    public function testRequestMethodGet()
    {
        $request = new Request();
        $request->setMethod(RequestMethod::GET);
        $this->assertTrue($request->isGet());
        $this->assertFalse($request->isPatch());
    }

    public function testRequestMethodPatch()
    {
        $request = new Request();
        $request->setMethod(RequestMethod::PATCH);
        $this->assertTrue($request->isPatch());
        $this->assertFalse($request->isOptions());
    }

    public function testRequestMethodOptions()
    {
        $request = new Request();
        $request->setMethod(RequestMethod::OPTIONS);
        $this->assertTrue($request->isOptions());
        $this->assertFalse($request->isHead());
    }

    public function testRequestMethodHead()
    {
        $request = new Request();
        $request->setMethod(RequestMethod::HEAD);
        $this->assertTrue($request->isHead());
        $this->assertFalse($request->isPut());
    }

    public function testRequestMethodPut()
    {
        $request = new Request();
        $request->setMethod(RequestMethod::PUT);
        $this->assertTrue($request->isPut());
        $this->assertFalse($request->isPost());
    }

    public function testSetGetRequestUri()
    {
        $request = new Request();
        $request->setRequestUri('/teapot?not=kettle');
        $this->assertEquals('/teapot?not=kettle', $request->getRequestUri(true));
        $this->assertEquals('/teapot', $request->getRequestUri());
    }

    public function testSetGetPost()
    {
        $subTest = ['1', '2', '3'];
        $postTest = ['a' => 'val', 'b' => '', 'c' => $subTest];

        $request = new Request();
        $request->setPostData($postTest);

        $this->assertEquals('123', $request->getPost('bogus', '123'));
        $this->assertEquals('val', $request->getPost('a', '123'));
        $this->assertEquals('', $request->getPost('b', '123'));
        $this->assertEquals(null, $request->getPost('c'));

        $this->assertEquals($subTest, $request->getPostArray('c'));
        $this->assertEquals(null, $request->getPostArray('a'));

        $this->assertEquals($postTest, $request->getPostData());
    }

    public function testSetGetQuery()
    {
        $subTest = ['1', '2', '3'];
        $testQueryParams = ['a' => 'val', 'b' => ''];

        $request = new Request();
        $request->setQueryData($testQueryParams);

        $this->assertEquals('123', $request->getQueryParam('bogus', '123'));
        $this->assertEquals('val', $request->getQueryParam('a', '123'));
        $this->assertEquals('', $request->getQueryParam('b', '123'));
        $this->assertEquals($testQueryParams, $request->getQueryParams());
    }

    public function testSetGetEnvironment()
    {
        $testEnvironment = ['a' => 'val', 'b' => ''];

        $request = new Request();
        $request->setEnvironmentData($testEnvironment);

        $this->assertEquals('123', $request->getEnvironment('bogus', '123'));
        $this->assertEquals('val', $request->getEnvironment('a', '123'));
        $this->assertEquals('', $request->getEnvironment('b', '123'));
        $this->assertEquals($testEnvironment, $request->getEnvironmentData());
    }

    public function testSetGetCookies()
    {
        $testCookies = ['a' => 'val', 'b' => ''];

        $request = new Request();
        $request->setCookieData($testCookies);

        $this->assertEquals('123', $request->getCookie('bogus', '123'));
        $this->assertEquals('val', $request->getCookie('a', '123'));
        $this->assertEquals('', $request->getCookie('b', '123'));
        $this->assertEquals($testCookies, $request->getCookies());
    }

    /**
     * @depends testSetGetRequestUri
     * @depends testRequestMethodPatch
     * @runInSeparateProcess
     */
    public function testExtractFromEnvironment()
    {
        $_GET = ['test' => 'abc'];
        $_POST = ['abc' => 'test'];
        $_SERVER = ['REQUEST_URI' => '/pots?test=abc', 'REQUEST_METHOD' => RequestMethod::PATCH];
        $_COOKIE = ['Session' => uniqid()];
        $_FILES = [
            'badKey' => [
                'name' => 'missingSomeData'
            ],
            'goodKey' => [
                'name' => 'bookmarks.html',
                'type' => 'text/html',
                'tmp_name' => '/tmp/php3D.tmp',
                'error' => UPLOAD_ERR_OK,
                'size' => 644563
            ]
        ];

        $request = Request::extractFromEnvironment();

        $this->assertTrue($request->isPatch());
        $this->assertEquals('/pots', $request->getRequestUri());
        $this->assertEquals('test', $request->getPost('abc', 'POST_DEF'));
        $this->assertEquals('abc', $request->getQueryParam('test', 'QUERY_DEF'));
        $this->assertEquals('PATCH', $request->getEnvironment('REQUEST_METHOD'));
        $this->assertEquals($_COOKIE['Session'], $request->getCookie('Session'));
        $this->assertCount(1, $request->getFileUploads());

        $files = $request->getFileUploads();
        $file = array_shift($files);

        $this->assertEquals('text/html', $file->getType());
    }

    public function testUploadProcessing()
    {
        $files = [
            'badKey' => [
                'name' => 'missingSomeData'
            ],
            'goodKey' => [
                'name' => 'bookmarks.html',
                'type' => 'text/html',
                'tmp_name' => '/tmp/php3D.tmp',
                'error' => UPLOAD_ERR_OK,
                'size' => 644563
            ]
        ];

        $request = new Request();
        $this->assertCount(0, $request->getFileUploads());
        $request->setFileData($files);

        $this->assertCount(1, $request->getFileUploads());
        $this->assertTrue(isset($files['goodKey']), $request->getFileUploads());

        $files = $request->getFileUploads();
        $file = array_shift($files);

        $this->assertEquals('bookmarks.html', $file->getOriginalName());
        $this->assertEquals('text/html', $file->getType());
        $this->assertEquals('/tmp/php3D.tmp', $file->getTemporaryPath());
        $this->assertEquals(UPLOAD_ERR_OK, $file->getError());
        $this->assertEquals(0, $file->getFileSize());
    }

    public function testMultiFileUploadProcessing()
    {
        $files = [
            'combinedFiles' => [
                'name' => ['one.jpg', 'two.jpg'],
                'type' => ['image/jpg', 'image/jpeg'],
                'tmp_name' => ['/tmp/php3A.tmp', '/tmp/php3B.tmp'],
                'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_EXTENSION],
                'size' => [644563, 365446]
            ],
            'anotherLooseFile' => [
                'name' => 'bookmarks.html',
                'type' => 'text/html',
                'tmp_name' => '/tmp/php3D.tmp',
                'error' => UPLOAD_ERR_OK,
                'size' => 644563
            ]
        ];

        $request = new Request();
        $request->setFileData($files);

        $ups = $request->getFileUploads();

        $this->assertCount(3, $ups);

        $fileOne = array_shift($ups);
        $this->assertEquals('combinedFiles', $fileOne->getFormKey());
        $this->assertEquals('one.jpg', $fileOne->getOriginalName());

        $fileTwo = array_shift($ups);
        $this->assertEquals('combinedFiles', $fileTwo->getFormKey());
        $this->assertEquals('two.jpg', $fileTwo->getOriginalName());

        $fileThree = array_shift($ups);
        $this->assertEquals('anotherLooseFile', $fileThree->getFormKey());
        $this->assertEquals('bookmarks.html', $fileThree->getOriginalName());
    }

    public function testHeaderParseAndGet()
    {
        $request = new Request();

        $request->setEnvironmentData([
            'REQUEST_METHOD' => 'POST',
            'HTTP_FAKE_ASS_HEADER' => 'testing One TWO Three ',
            'HTTP_X_FORWARDED_FOR' => '127.0.0.1'
        ]);

        $expectedHeaders = [
            'Fake-Ass-Header' => 'testing One TWO Three ',
            'X-Forwarded-For' => '127.0.0.1'
        ];

        $this->assertEquals($expectedHeaders, $request->getHeaders(), 'Headers should be parsed correctly, and their casings modified appropriately. Values should not change.');
        $this->assertEquals(null, $request->getHeader('Request-Method', null, 'Only HTTP_ prefixed $_SERVER data should be considered a header'));
        $this->assertEquals($expectedHeaders['X-Forwarded-For'], $request->getHeader('X-Forwarded-For'));
        $this->assertEquals($expectedHeaders['Fake-Ass-Header'], $request->getHeader('Fake-Ass-Header'));
        $this->assertEquals($expectedHeaders['X-Forwarded-For'], $request->getHeader('x-forwarded-for', 'getHeader() should be case insensitive'));
    }

    public function testIsHttpsForNonEmpty()
    {
        $request = new Request();
        $request->setEnvironmentData([
            'HTTPS' => 'NonEmptyValue'
        ]);
        $this->assertTrue($request->isHttps());
        $this->assertEquals('https', $request->getProtocol());
    }

    public function testIsHttpsForEmpty()
    {
        $request = new Request();
        $request->setEnvironmentData([
            'HTTPS' => ''
        ]);
        $this->assertFalse($request->isHttps());
        $this->assertEquals('http', $request->getProtocol());
    }

    public function testIsHttpsForOffIIS()
    {
        // when using ISAPI with IIS, the value will be off if the request was not made through the HTTPS protocol.
        $request = new Request();
        $request->setEnvironmentData([
            'HTTPS' => 'off'
        ]);
        $this->assertFalse($request->isHttps());
        $this->assertEquals('http', $request->getProtocol());
    }

    public function testIsAjax()
    {
        $request = new Request();
        $request->setEnvironmentData([
            'HTTP_X_REQUESTED_WITH' => 'xmlHttpREQUEST'
        ]);
        $this->assertTrue($request->isAjax(), 'Ajax requests should be determined, without case sensitivity, based on X-Requested-With equaling XMLHttpRequest');
    }

    public function testIsNotAjax()
    {
        $request = new Request();
        $request->setEnvironmentData([
            'HTTP_X_REQUESTED_WITH' => 'bogus'
        ]);
        $this->assertFalse($request->isAjax());
    }

    public function testGetIp()
    {
        $request = new Request();
        $request->setEnvironmentData([
            'REMOTE_ADDR' => '8.8.4.4',
            'HTTP_X_FORWARDED_FOR' => '8.8.8.8'
        ]);
        $this->assertEquals('8.8.4.4', $request->getIp(), 'REMOTE_ADDR should always be used; X_FORWARDED for should be ignored.');
    }

    public function testIsIpv6WithIpv4()
    {
        $request = new Request();
        $request->setEnvironmentData([
            'REMOTE_ADDR' => '8.8.4.4',
            'HTTP_X_FORWARDED_FOR' => '::1'
        ]);
        $this->assertFalse($request->isIpv6());
    }

    public function testIsIpv6WithIpv6()
    {
        $request = new Request();
        $request->setEnvironmentData([
            'REMOTE_ADDR' => '0:0:0:0:0:127.0.0.1',
            'HTTP_X_FORWARDED_FOR' => '8.8.8.8'
        ]);
        $this->assertTrue($request->isIpv6());
    }

    public function testGetReferrer()
    {
        $request = new Request();
        $this->assertEquals(null, $request->getReferrer());
        $request->setEnvironmentData([
            'HTTP_REFERRER' => 'http://my.uri.com/bla.html'
        ]);
        $this->assertEquals('http://my.uri.com/bla.html', $request->getReferrer());
    }

    public function testGetUserAgent()
    {
        $request = new Request();
        $this->assertEquals(null, $request->getUserAgent());
        $request->setEnvironmentData([
            'HTTP_USER_AGENT' => 'MyBrowser (v1.0; test; hello world)'
        ]);
        $this->assertEquals('MyBrowser (v1.0; test; hello world)', $request->getUserAgent());
    }

    public function testGetHostname()
    {
        $request = new Request();
        $this->assertEquals(null, $request->getHostname());
        $request->setEnvironmentData([
            'HTTP_HOST' => 'your.web.com'
        ]);
        $this->assertEquals('your.web.com', $request->getHostname());
    }

    public function testGetPort()
    {
        $request = new Request();
        $this->assertEquals(80, $request->getPort(), 'Default port should be 80 (HTTP)');
        $request->setEnvironmentData([
            'HTTPS' => 'On',
            'SERVER_PORT' => '8090'
        ]);
        $this->assertEquals(8090, $request->getPort());
    }

    public function testGetUrl()
    {
        $request = new Request();
        $request->setRequestUri('/web/page.html?action=eat&what=pie');
        $request->setEnvironmentData([
            'HTTPS' => 'On',
            'SERVER_PORT' => 1337,
            'HTTP_HOST' => 'web.com'
        ]);

        $expectedFullUrl = 'https://web.com:1337/web/page.html';
        $expectedFullUrlWithParams = $expectedFullUrl . '?action=eat&what=pie';

        $this->assertEquals($expectedFullUrl, $request->getUrl(false));
        $this->assertEquals($expectedFullUrlWithParams, $request->getUrl(true));
        $this->assertEquals($expectedFullUrlWithParams, $request->getUrl(), 'Params should be included by default');

        $request->setEnvironmentData([
            'HTTPS' => 'On',
            'SERVER_PORT' => 443,
            'HTTP_HOST' => 'web.com'
        ]);

        $expectedFullUrl = 'https://web.com/web/page.html';
        $this->assertEquals($expectedFullUrl, $request->getUrl(false), 'Regular port numbers should be hidden');
    }
}