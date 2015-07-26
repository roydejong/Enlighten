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

    use Enlighten\Enlighten;

    include '../vendor/autoload.php';

    $app = new Enlighten();

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

    $app->start();