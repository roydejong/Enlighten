Welcome to Enlighten's documentation!
=====================================

What is Enlighten?
^^^^^^^^^^^^^^^^^^
**Enlighten is a micro framework that helps you rapidly build PHP web applications.**

It takes the pain out of parsing and routing requests, handling form submissions, taking care of sessions and authentication and quickly getting an application up and running.

.. code-block:: php

    $app = new Enlighten();

    $app->get('/hello/$name', function ($name) {
        echo "Hello there, $name";
    });

In short: a fat-free framework with very few external dependencies that helps you *get shit done* - without getting in your way.

Enlighten is an open source, MIT licensed project. Check it out on GitHub:

https://github.com/roydejong/Enlighten


The documentation
^^^^^^^^^^^^^^^^^
You can always find the latest documentation on readthedocs.org:

https://enlighten.readthedocs.org/en/latest/