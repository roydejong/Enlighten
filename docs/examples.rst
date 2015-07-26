Code Examples
=============

"Hello world" application
^^^^^^^^^^^^^^^^^^^^^^^^^

.. code-block:: php

    <?php

    use Enlighten\Enlighten;

    include '../vendor/autoload.php';

    $app = new Enlighten();
    $app->get('/', function () {
        echo "Hello, World!";
    });
    $app->start();