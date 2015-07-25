Enlighten Documentation
=====================================

.. toctree::
    :hidden:

    quickstart

What is Enlighten?
------------------
**Enlighten is a micro framework that helps you rapidly build PHP web applications.**

Take the pain out of the common tasks you have to go through when you build any PHP application:

.. code-block:: php

    $app = new Enlighten();

    $app->get('/hello/$name', function ($name) {
        echo "Hi there, $name";
    });

In short: a fat-free framework with no external dependencies that simply helps you *get shit done* without getting in your way:

- Deal with parsing requests with ease: cookies, file uploads, headers, forms and more.
- Set up flexible and easy to use routing for requests, with dynamic URL variables and dependency injection.
- Apply filter functions for routing events and error handling; great for common tasks like authentication and logging.
- Control every aspect of the HTTP response sent back to the user, with an easy to use API.

Enlighten is an open source, MIT licensed project. Check it out on GitHub:

https://github.com/roydejong/Enlighten


The documentation
-----------------
You can always find the latest documentation on *readthedocs.org*:

https://enlighten.readthedocs.org/en/latest/

.. tip::

    Want to see why Enlighten is awesome, and start building cool stuff right away?
    Check out the aptly named :doc:`Quickstart <quickstart>` section for the quick and dirty code examples.

Support
-------
If you have found an issue, have an idea, or want to ask a question, please do so by creating a GitHub ticket:

https://github.com/roydejong/Enlighten/issues