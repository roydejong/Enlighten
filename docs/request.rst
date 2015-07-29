Request
=======

The ``Request`` class lets you parse and examine the HTTP request that the user sent to your web server.


.. tip::

    Instead of accessing PHP superglobals like ``$_POST``, ``$_GET``, ``$_SERVER``, ``$_COOKIES``, etc. you should always use the Request object -- it is a safer, more consistent and more convenient way of dealing with user-submitted data.

Accessing Request
^^^^^^^^^^^^^^^^^

The ``Request`` object is made available through the `Application Context`_. You can access it through your filter functions and target functions by adding it to your parameter list. Enlighten will inject the dependency where it is needed.

.. code-block:: php

    <?php

    new Route('/', function (Request $request) {
        echo $request->getRequestUri();
    });

.. _`Application Context`: application.html#context

By default, a request object is generated when you use the ``Enlighten`` class based on the current PHP environment and superglobals. You can also initialize and set a custom request by calling ``$app->setRequest()``. If you do not use the ``Enlighten`` class you can parse your own request by calling ``Request::extractFromEnvironment()``.

Reading data
^^^^^^^^^^^^

Users can submit data to your application in a variety of ways. For example, they may use a ``POST`` request to submit a form. Or they may use a ``GET`` request and supply query string parameters. Here's a summary of the options, and what they are called in Enlighten:

- Posted values: User-submitted data in ``POST``, ``PUT`` or ``PATCH`` requests. Called ``$_POST`` in vanilla PHP.
- Query string parameters: Parameters added in the Request URL by the user - in any request type. Misleadingly called ``$_GET`` in vanilla PHP.
- Cookies: Little bits of key/value data that are stored on the client computer. Called ``$_COOKIE`` in vanilla PHP.
- Uploads: Files that the user uploaded as part of their request. Called ``$_FILES`` in vanilla PHP.
- Headers: A variety of request headers that typically contain technical data. Hidden away in ``$_SERVER`` in vanilla PHP.

**Reading posted values and query parameters**

- You can read a single value by calling ``getPost($key)`` or ``getQueryParam($key)``. This function will try to look up the appropriate value and return a `string`. If your value cannot be located, ``NULL`` will be returned.
- You can also supply a default value when that will be returned instead of ``NULL`` when your given ``$key`` cannot be located: use ``getPost($key, $defaultValue)`` or ``getQueryParam($key, $defaultValue)``.
- You can get a full key/value array of all raw values by calling ``getPostData()`` or ``getQueryParams()``.

.. code-block:: php

    <?php

    // Read a single value. Will return NULL if the "name" key is not found.
    $name = $request->getPost('name');

    // Read a single post value. Will return 18 if the "age" key is not found.
    $age = intval($request->getPost('age', 18));

    // Retrieve a key => value array of all POST values.
    $postArray = $request->getPostData();

    foreach ($postArray as $key => $value) {
        // ...
    }

Working with URLs
^^^^^^^^^^^^^^^^^

When a user submits a request to your web server, they do so for a specific hostname (for example, the name or IP address of your website) and a request URL (the page they wish to access on your website - including query string parameters).

Typically, when you configure your web server you will let it determine which website to serve based on its hostname. Your application then simply interprets the request URL (everything starting at the ``/``). The :doc:`Routing <router>` module helps you set this up - it maps an incoming request to a piece of code in your application.

The Request class offers you some utilities for reading out this data:

- ``getProtocol()``: Gets the protocol string, either "http" or "https", that was used to access your website. You can also use ``isHttps()`` to determine whether HTTPS was used.
- ``getHostname()``: Gets the requested hostname (e.g. "google.com").
- ``getPort()``: Gets the port number that was used to talk to your web server (e.g. 80, 443 or a non-standard port like 8080).
- ``getRequestUri($includeQueryString = false)``: Gets relative request URL (e.g. "/my/page.html") and, optionally, query string.
- ``getUrl($includeQueryString = true)``: Gets the full current URL - including protocol, hostname, port (if it is non-standard), request URL and, optionally, query string.

