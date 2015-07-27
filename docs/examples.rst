Code Examples
=============

"Hello world" application
^^^^^^^^^^^^^^^^^^^^^^^^^

A simple ``index.php`` script that will simply set up :doc:`an Enlighten application <application>` and print out "Hello, World!" when opening the root page.

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

Note that you'll need to configure_ your server to forward all requests to this script first.

.. _configure: quickstart.html#configuring-your-web-server

Forms
^^^^^

This code shows you how you can deal with form submissions.

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

The framework offers some basic error pages by default, but you can override them by using :doc:`filters` on your application object.

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

If one of your filter functions causes any output to the body, the framework's default error pages will be suppressed.

Read and set cookies
^^^^^^^^^^^^^^^^^^^^

The **Request** and **Response** objects can be used for reading and writing cookies, respectively.

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

The **Request** class has an easy to use facility for safely processing file uploads.

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

You can call the ``saveTo()`` function multiple times if you want more than one copy of a file.

It's a good idea to always generate your own file names, as the user-supplied filename (``$file->getOriginalName()``) is not necessarily safe to use, and is not unique either.