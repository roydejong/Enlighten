Code Examples
=============

"Hello world" application
^^^^^^^^^^^^^^^^^^^^^^^^^

.. code-block:: php

    <?php

    use Enlighten\Enlighten;

    include '../vendor/autoload.php';

    $app = new Enlighten();

    // Say "Hello, World!" for all GET requests to "/"
    $app->get('/', function () {
        echo "Hello, World!";
    });

    $app->start();

Custom error pages
^^^^^^^^^^^^^^^^^^

.. code-block:: php

    <?php

    // Generic error handler
    $app->onException(function (\Exception $ex) {
        echo "Sorry, something went wrong!";
        echo $ex->getMessage();
    });

    // 404 / route not found error handler
    $app->notFound(function (Request $request) {
        echo "Sorry, but the page you requested could not be found!";
        echo "You requested: " . $request->getRequestUri();
    });

Read and set cookies
^^^^^^^^^^^^^^^^^^^^

.. code-block:: php

    <?php

    $app->get('/', function (Request $request, Response $response) {
        // Iterate all cookies
        $cookies = $request->getCookies();

        foreach ($cookies as $name => $value) {
            echo "Cookie $name = $value" . PHP_EOL;
        }

        // Set some cookies (follows same format as php set_cookie)
        $response->setCookie('SomeCookie', 'SomeValue', time() + 60, '/');
    });