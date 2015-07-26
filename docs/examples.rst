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

Forms
^^^^^

.. code-block:: php

    <?php

    $app->get('/', function () {
        // View logic goes here: display your form
    });

    $app->post('/', function (Request $request) {
        $name = $request->getPost('name', 'John Doe');
        echo "Hi there, $name";
    });


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

Handle file uploads
^^^^^^^^^^^^^^^^^^^

.. code-block:: php

    <?php

    $app->post('/', function (Request $request) {
        // Iterate all uploaded files
        $files = $request->getFileUploads();

        foreach ($files as $file) {
            // Is this file okay?
            if ($file->hasError()) {
                echo $file->getErrorMessage();
                continue;
            }

            // Let's move it to our uploads directory
            $filename = uniqid() . '.tmp';
            $file->saveTo("./uploads/$filename");
        }
    });