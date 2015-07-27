Application
===========

Enlighten app
^^^^^^^^^^^^^
The easiest way to get started with the framework is to use the **Enlighten** class. This class represents the core of your application and ties all the framework components together in one easy to use package.

The flow for setting up an Enlighten application is as follows:

1. Initialize a new Enlighten application: ``new Enlighten();``.
2. Configure your application as you please: register filters, routes and settings.
3. Call the ``start()`` function on your application instance.

A typical ``index.php`` might look like this:

.. code-block:: php

    <?php
    
    use Enlighten\Enlighten;
    
    include '../vendor/autoload.php';
    
    $app = new Enlighten();
    // (Register routes and settings here)
    $app->start();
    
The ``start()`` function begins processing the incoming request, and will ultimately send a response back to the user.
    
Application flow
^^^^^^^^^^^^^^^^
A typical application flow will like this:

1. The user submits an incoming HTTP request to your web server (e.g. nginx or apache).
2. Your web server proxies all requests (to non-static files) through to your ``index.php`` file. This file then initializes and configures your application.
3. Enlighten parses the incoming HTTP request and passes it through to the :doc:`router` (when you call ``$app->start()``).
4. If the request matches a route, that route's target function will be called (for example, a Closure or a function within a controller).
5. A response is gradually built up by your application code, and possibly modified by your filters. At the end of the run, the request is sent back to the user.

If you do not use the ``Enlighten`` class, this flow probably still applies to you; you'll just have to do a bit more work to tie all the components together.

Configuration
^^^^^^^^^^^^^

The ``Enlighten`` class offers a few helpful utilities for configuring your application. You can call these functions at any time before you call the `start()` function.

**Overrides**

By default, Enlighten does some of the initialization work for you. You can override certain components as needed.

- You can set a custom :doc:`router` class by using the ``setRouter($router)`` function.
- You can set a custom HTTP request object by using the ``setRequest($request)`` function.

**Routing**

.. tip::

    For more information on setting up and configuring routes, check out the :doc:`main article <router>`.

You can either define and manage a custom router, or use the utilities in the ``Enlighten`` class to set up routing. You can either use the ``$app->route($pattern, $target)`` function to set up a route that matches requests of any method; or use a specific function like, for example, ``$app->post($pattern, $target)`` to set up a route for the POST request method only.

.. code-block:: php

    <?php

    $app = new Enlighten();

    // Create a route that matches all request methods
    $route = $app->route('/my/page', function () {
        echo "Welcome to my route!";
    });

    // And register a filter on it, while we're here!
    $route->after(function () {
        echo "And see you later!";
    });

    // Create a route specifically for the POST request method
    $app->post('/my/form', function () {
        echo "Thanks for the post!";
    });

    // There's a function for each request method! e.g. post(), get(), put(), delete(), ...

You can set up a subdirectory for your application here as well:

.. code-block:: php

    <?php

    $app = new Enlighten();
    $app->setSubdirectory('/projects/myapp');

**Filters**

.. tip::

    For more information on setting up and configuring filters, check out the :doc:`main article <filters>`.

You can hook in to certain application events by attaching filter functions to them. These filter functions have access to the Context (see below).

- ``before()``: Called before routing occurs, and before any route is matched.
- ``after()``: Called after routing has finished, and before the request is sent. Not used when an exception occurs.
- ``onException()``: Called if an Exception is raised during application execution.
- ``notFound()``: Found when no suitable route can be found. Note that both ``before()`` and ``after()`` are still called as well.

.. code-block:: php

    <?php

    $app = new Enlighten();
    $app->notFound(function (Request $request) {
        echo "Sorry, but that page is not here: " . $request->getRequestUri();
    });

You can also apply filter functions to specific routes rather than the application scope. Check out the :doc:`routing docs <router>` for details.


Context
^^^^^^^
The application context (class ``Context``) is a collection of data that represents the current state of the application. At its core, it is simply a bag of objects that is filled up and passed around the application, constantly being fed with the most up-to-date information.

The magic comes from its ability to intelligently inject its contents into a function based on its parameter list. For example, if the context contains a `Request` object, and a function requests that type of data in its parameter list, it can be passed as a value to that function. This is used throughout the framework as a way to flexibly pass data on-demand without making your code more verbose.

The ``Enlighten`` class normally initializes and manages its own Context, which you cannot directly modify at configuration time. If you manage your own application flow, you'll need to set up your own context to pass around. Because the `Context` class is completely generic you could also apply it to a variety of other uses.

.. code-block:: php

    <?php

    $app->get('/hello/$name', function (Request $request, $name, Response $response) {
        // Read a posted value
        $age = intval($request->getPost('age', 18));

        // Manipulate the response code
        $this->response->setResponseCode(ResponseCode::HTTP_IM_A_TEAPOT);

        // Say hello to the user
        echo "Hi there, $name. You are $age years old.";
    });

We will inject the appropriate variables based on the parameters you define in your function. The order doesn't matter. For :doc:`routing <router>` variables, make sure the name matches. For other variables, make sure the type is correct. If we can't resolve a variable, a ``NULL`` value will be passed.

**What does the Context contain?**

The ``Enlighten`` class will always publish the following data to the context it manages:

- **Enlighten**: The application instance itself.
- **Request**: The parsed incoming HTTP request object.
- **Response**: The HTTP response that is being built up.
- **Router**: The router managed by the application.
- **Route**: The route that matched - if one was successfully matched.
- **Exception**: The last exception that was raised if there was one - particularly useful for ``onException()`` filters.

**Managing a context**

.. code-block:: php

    <?php

    $context = new Context();

    // Register a Request object to our Context
    $request = new Request();
    $context->registerInstance($context);

When you register an object to a context, it will override any previous registrations of the same type. In the example above, if we had previously set a ``Request`` on the context, it has now been replaced.

If the object that you have registered has parent classes, *weak links* will be created for those classes as well. That means if you register an ``InvalidArgumentException``, a weak link will also be created for ``Exception``. So when a function asks for an ``Exception``, they may still get the most recent compatible object instead - an ``InvalidArgumentException`` in this example. However, if an exact match is available in the Context, that object will always be used instead.

Note that you can currently only register *objects* to a context; primitive types by variable name are not supported at this time.

**Manual injection**

When you have a context, you will also want to use its superpowers to inject its values to a function. All you need is a *callable* function with a parameter list.

.. code-block:: php

    <?php

    // Set up our context
    $context = new Context();
    $context->registerInstance($request);
    $context->registerInstance($response);

    // Let's define our Closure that will receive dependency injection.
    $sampleFunction = function (Request $request, $randomVar, Response $response) {
        // ...
    });

    // Use the context to determine parameters, and call the function
    $params = $context->determineParamValues($sampleFunction);
    call_user_func_array($sampleFunction, $params);

This is the only way you can currently extract values from a context.

Quirks
^^^^^^

Here is an overview of quirks that you may need to know about when using the ``Enlighten`` class:

- If any output is sent after ``start()`` is called and before the HTTP response is sent back, it will be appended to the end of the response body "just in time". That means you can use `echo` freely.
- If an error occurs (including 404 errors), the output buffer is cleared and the request is emptied. Any output sent by your filter functions, for example, will be discarded.
- ``after()`` filters have the final say on any output that is sent out - their output is never discarded. But: they will not be called if an exception occurs in your application.
- If your :doc:`router` is empty, a default "Welcome to Enlighten" page will be shown.