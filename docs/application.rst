Application
===========

Enlighten app
^^^^^^^^^^^^^
The easiest way to get started with the framework is to use the **`Enlighten`** class. This class represents the core of your application and ties all the framework components together in one easy to use package.

The flow for setting up an Enlighten application is as follows:

1. Initialize a new Enlighten application: `new Enlighten();`.
2. Configure your application as you please: register filters, routes and settings.
3. Call the `start()` function on your application instance.

A typical index.php might look like this:

.. code-block:: php

    <?php
    
    use Enlighten\Enlighten;
    
    include '../vendor/autoload.php';
    
    $app = new Enlighten();
    // (Register routes and settings here)
    $app->start();
    
You do not need to use the Enlighten class; you may also choose to manage your own application flow. 
    
Application flow
^^^^^^^^^^^^^^^^
A typical application flow will like this:

1. The user submits an incoming HTTP request to your web server (e.g. nginx or apache).
2. Your web server proxies all requests (to non-static files) through to your `index.php` file. This file then initializes and configures your application.
3. Enlighten parses the incoming HTTP request and passes it through to the :doc:`router`.
4. If the request matches a route, that route's target function will be called (for example, a Closure or a function within a controller).
5. A response is gradually built up by your application code, and possibly modified by your filters. At the end of the run, the request is sent back to the user.

If you do not use the `Enlighten` class, this flow probably still applies to you; you'll just have to do a bit more work to tie all the components together.

Context
^^^^^^^
The application context (class `Context`) is a collection of data that represents the current state of the application. At its core, it is simply a bag of objects that is filled up and passed around the application, constantly being fed with the most up-to-date information.

The magic comes from its ability to intelligently inject its contents into a function based on its parameter list. For example, if the context contains a `Request` object, and a function requests that type of data in its parameter list, it can be passed as a value to that function. This is used throughout the framework as a way to flexibly pass data on-demand without making your code more verbose.

The `Enlighten` class normally initializes and manages its own Context, which you cannot directly modify at configuration time. If you manage your own application flow, you'll need to set up your own context to pass around. Because the `Context` class is completely generic you could also apply it to a variety of other uses.

**Contents**

The `Enlighten` class will publish the following data to the context it manages:

- **Enlighten**: The application instance itself.
- **Request**: The parsed incoming HTTP request object.
- **Response**: The HTTP response that is being built up.
- **Router**: The router managed by the application.
- **Route**: If a route was matched, it will be registered to the context.
- **Exception**: For `onException` filters, the relevant exception object will be set.

**Managing a context**

.. code-block:: php

    <?php

    $context = new Context();

    // Register a Request object to our Context
    $request = new Request();
    $context->registerInstance($context);

When you register an object to a context, it will override any previous registrations of the same type. In the example above, if we had previously set a `Request` on the context, it has now been replaced.

If the object that you have registered has parent classes, *weak links* will be created for those classes as well. That means if you register an `InvalidArgumentException`, a weak link will also be created for `Exception`. So when a function asks for an `Exception`, they may still get the most recent compatible object instead. However, if an exact match is available in the Context, that object will always be used instead.

You can only register *objects* to a context; primitive types are not supported at this time.

**Context injection**

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