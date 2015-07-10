Quickstart
==========
This guide will help you set up Enlighten and start using its powerful tools.

Installation
^^^^^^^^^^^^
Enlighten is a composer library. It couldn't be easier to install: set up your project, initialize composer, and require the framework as a dependency:

.. code-block:: bash

    composer require enlighten/framework
    
Your project is now ready to use Enlighten. Not bad, right?

Set up your application
^^^^^^^^^^^^^^^^^^^^^^^
The ``Enlighten`` class acts as the heart of your application. It ties all the framework's components together into one easy to use package: from request processing and routing to sending a response back to the user.

.. tip::

    Not interested in letting Enlighten handle your entire application flow? The rest of this guide is not for you. Refer to the documentation for the individual components you'd like to use instead.  
    
To get started, you will want to initialize the composer autoloader and initialize a new instance of `Enlighten`. Here's what a typical ``index.php`` might look like:

.. code-block:: php

    <?php

    use Enlighten\Enlighten;

    include '../vendor/autoload.php';

    $app = new Enlighten();

    $app->get('/', function () {
        echo 'Hello world!';
    });

    $app->start();
    
This snippet of code will simply print out the text ``Hello world!`` when you open ``index.php``.

To summarize, here's what we've done so far:

- Initialize composer's autoloader, which will make our `use` statement work.
- Initialize a new application instance (``new Enlighten()``) with a blank configuration.
- Register a new **Route** for all ``GET`` requests sent to ``/``, with a function.
- Start the application: parse the incoming request, route it to our function, and send a response back.

All that in just a few lines of code. This is just a basic example: we have many more power tools at our disposal to do more cool stuff.
 
Router configuration
^^^^^^^^^^^^^^^^^^^^
**Request methods**

The `Enlighten::get($pattern)` function we used in the example above registers a new route that only applies to `GET` requests. There are appropriate functions for all other common request methods as well, such as `Enlighten::post($pattern)`.

If you'd like to register a route that applies to all request methods, you can use the `route` function instead:

.. code-block:: php

    $app->route('/', function () {
        // This function will be called irregardless of request method (GET, POST, etc)
        echo "Hello world";
    });
    
**Routing patterns**

When you register a new route, you have to define a **pattern**. This is what incoming requests are matched against. There are a few cool things you can do with these patterns.

You can use Regex patterns for a bit more flexibility:

.. code-block:: php

    $app->route('/(index|home)(/)', function () {
        // Matches "/index" or "/home", with an optional trailing slash
    });
    
You can also define dynamic variables in your route definitions which you can then retrieve in your functions:

.. code-block:: php

    $app->get('/users/view/$id', function ($id) {
        echo "You asked to GET a user with ID $id";
    });
    
