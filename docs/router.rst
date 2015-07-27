Router
======

A router manages a collection of routes. Each route maps an incoming request to an appropriate target function. It is an essential component that lets your application respond to user's requests intelligently.

Creating a router
^^^^^^^^^^^^^^^^^

.. tip::

    Under normal circumstances, the ``Enlighten`` :doc:`application` class will initialize and manage a default router for you - you do not necessarily need to initialize or manage one yourself.

If you want more control over the router and its routes, or use a custom implementation of the ``Router`` class, you will need to manage it yourself:

.. code-block:: php

    <?php

    // Initialize a custom router
    $router = new Router();
    
    // Assign our router to our application
    $app = new Enlighten();
    $app->setRouter($router);
    $app->start();
     
Configuring routes
^^^^^^^^^^^^^^^^^^
You can define a route by simply intializing it. A basic route always consists of a **pattern** (what to match) and a **target function** (what should this route do when matched?):

.. code-block:: php

    <?php

    // Create our route
    $route = new Route('/users/$user/$action', function ($user, $action) {
        // Will match /users/admin/view
    });

    // Register it to our router
    $router->register($route);
    
When you use the ``Enlighten`` class - either with the default router or after registering your own router - you can also use the ``route()`` function. This function also returns the created `Route` object so you can customize it further as needed - even without ever having to manage your own Router.

.. code-block:: php

    <?php

    $app->route('/users/$user/$action', function ($user, $action) {
        // ...
    });
    
In addition, the ``Enlighten`` class also offers some utility functions that let you register routes with request method constraints. There are utility functions for the ``GET``, ``POST``, ``PUT``, ``PATCH``, ``OPTIONS`` and ``DELETE`` methods. The ``route()`` function does not apply such a constraint, so it will match any method by default.

.. code-block:: php

    <?php

    // For example, register a route with a GET request method constraint:
    $app->get('/sample', ...);
    
    // Or a DELETE constraint:
    $app->delete('/sample', ...);
    
You can clear a router by calling ``$router->clear()``. You can assert whether any routes have been registered in a router via the ``$router->isEmpty()`` function.
    
Routing patterns
^^^^^^^^^^^^^^^^
The primary factor for any route is its pattern. A pattern indicates what the incoming Request's URL needs to be matched against for the route to be considered a successful match.

Pattern matching always occurs in groups (virtual URL levels separated by the `/`). All given pattern groups must be present for it to match. You can also use some simple Regex patterns in your route pattern.

URL variables
^^^^^^^^^^^^^
In addition to defining static routing patterns that need to match exactly, you can also make them dynamic by using URL variables.

You can use dynamic variables in your routing pattern by using the dollar sign `$` as a prefix. A variable must always span one entire URL group. `/bla/$var` is okay but `/bla$var` will not work as expected.

Dynamic variable values will be passed back to the target function via dependency injection:

.. code-block:: php

    <?php

    $app->route('/say/$string', function ($string) {
        // By defining $string as a parameter for your target function, the value of $string will be set to the corresponding URL group that was in the request URI that matched.
    });
    
It is not possible to apply any particular constraints to what is accepted as a value for a URL variable, so always make sure to carefully validate all values that are supplied by the user.

A URL variable does not make that part of the pattern optional.

Target functions
^^^^^^^^^^^^^^^^
A target function must either be **callable** or a **function definition string**. Here's an overview of the most common ways this is accomplished:

.. code-block:: php

    <?php

    // 1. Use a Closure function
    $app->route('/example', function () { });

    // 2. Use an Enlighten function definition string
    $app->route('/example', 'my\namespace\Class@myFunction');
    // Format: classNameWithNamespace@functionName

    // 3. Use a static class
    $app->route('/example', ['MyClassName', 'myFunctionName']);
    $app->route('/example', 'MyClassName::myFunctionName');
    
    // 4. Use an object function
    $app->route('/example', [$myClassObj, 'myFunctionName']);

Target functions have access to the `application context`_ and receive dependency injection - this allows them to retrieve certain objects on demand.

.. _`application context`: application.html#context

.. tip::

    This section only highlights some common techniques - PHP.net has more examples_ on other ways to define callable functions.

    .. _examples: https://secure.php.net/manual/en/language.types.callable.php

Using subdirectories
^^^^^^^^^^^^^^^^^^^^
The Router class supports operating out of a subdirectory. This can be useful if you want to run your entire application from a certain directory or with a certain prefix.

.. code-block:: php

    <?php

    // Either directly via a custom router
    $router->setSubdirectory('example');
    
    // ..or using the Enlighten class
    $app->setSubdirectory('example');
    
If you follow the above example, the router will assume that all your routes will begin with a "example" directory. For example, if you register a route for `/mypage` it will then only match against requests for `/example/mypage`.
    


