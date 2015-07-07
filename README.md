Enlighten Framework
===

[![Documentation Status](https://readthedocs.org/projects/enlighten/badge/?version=latest)](https://readthedocs.org/projects/enlighten/?badge=latest) [![Build Status](https://travis-ci.org/roydejong/Enlighten.svg?branch=master)](https://travis-ci.org/roydejong/Enlighten)

**Enlighten is a simple, lean, high-performance PHP micro framework that acts as the foundation for your web application.**

This is a modern framework for PHP 5 that doesn't get in your way. Just the building blocks you need to accelerate your application development and simply *get shit done*. 

- Easy HTTP request and response management.
- Razor fast routing with dynamic variables.

It is awesome because:

- Built for ease of use and performance.
- Low on fat: small code base with minimal external dependencies.
- Stable: tested extensively with a battery of unit tests.

Coming soon to Enlighten:

- MVC building blocks for a well-organized application.
- Form validation and on-the-fly form HTML generation.
- Filters for routing, e.g. for authentication.
- Cookie and session handling.
- More ways of parsing requests: files, headers, cookies.


**Note: This is pre-release information, this does not reflect the current or even a (planned) final state of the project. This is, right now, just an experiment.**

Getting started
---
To get started, add Enlighten as a Composer dependency to your project:

    composer require enlighten/framework

In the entry point (`index.php`) of your application, initialize and start Enlighten:

    $app = new Enlighten();
    $app->start();
    
You'll need to make sure that your web server redirects all requests you want to handle with Enlighten to this script. For example, it is common to redirect all requests that do not resolve to a static file to `index.php`, which then contains the code you see above.
    
This code will initialize a blank application and process the incoming request.

Defining routes
---
Next, you will want to define routes. Routes map an incoming request (for example, `/articles/hello-world`) to an appropriate function or controller that can respond to it (for example, the `viewArticle` function in the `ArticlesController`).

It's easy to set up routes. Let's see what the above example looks like in code:

    $app->get('/articles/$name', function ($name) {
        echo "You requested an article with this name: $name";
    });
    
Cool, huh? The dollar sign `$` indicates this URL contains a dynamic variable. This will cause any HTTP GET requests that match our pattern to be sent to the function you've defined, with the dynamic variables you have defined as parameters. You can pass any *callable* when you register a new route.

Helpful tip: Remember to use single quotes `'` rather than double quotes `"` when defining these patterns, or things will go bad when you try to use the dollar `$` sign!
 
Other functions you can use to register your routes are as follows:

- `$app->route($pattern, $target)`: The same as the above example, for requests of all method types.
- `$app->post($pattern, $target)`: The same as the above example, for POST requests
- `$app->put($pattern, $target)`: The same as the above example, for PUT requests
- `$app->patch($pattern, $target)`: The same as the above example, for PATCH requests
- `$app->head($pattern, $target)`: The same as the above example, for HEAD requests
- `$app->options($pattern, $target)`: The same as the above example, for OPTIONS requests
- `$app->delete($pattern, $target)`: The same as the above example, for DELETE requests
 
Further reading
---
If you need a bit more power and customization, or seek more information, check out the docs:

**<https://enlighten.readthedocs.org/en/latest/>**
