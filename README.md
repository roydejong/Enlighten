Enlighten Framework
===

[![Documentation Status](https://img.shields.io/badge/docs-latest-brightgreen.svg?style=flat)](http://enlighten.readthedocs.org/en/latest/)
[![Build Status](https://travis-ci.org/roydejong/Enlighten.svg?branch=master)](https://travis-ci.org/roydejong/Enlighten)
[![Latest Stable Version](https://poser.pugx.org/enlighten/framework/v/stable)](https://packagist.org/packages/enlighten/framework)
[![Latest Unstable Version](https://poser.pugx.org/enlighten/framework/v/unstable)](https://packagist.org/packages/enlighten/framework)
[![License](https://poser.pugx.org/enlighten/framework/license)](https://github.com/roydejong/Enlighten/blob/master/LICENSE.md)
[![Test Coverage](https://codeclimate.com/github/roydejong/Enlighten/badges/coverage.svg)](https://codeclimate.com/github/roydejong/Enlighten/coverage)

**Enlighten is a simple, lean, high-performance PHP micro framework that acts as the foundation for your web application.**

This is a modern framework for PHP 5 that doesn't get in your way. Just the building blocks you need to accelerate your application development and simply *get shit done*. 

- Easy HTTP request and response management: forms, headers, cookies, files and more.
- Razor fast routing with dynamic URL variables and dependency injection.
- Application and route filters for handling authentication, exceptions, etc.

It's easy to use:

    $app->get('/hello/$name', function ($name) {
        echo "Hi there, $name";
    });

It is awesome because it is:

- Built for ease of use and performance.
- Low on fat: small code base with minimal external dependencies.
- Stable: tested extensively with a battery of unit tests.
- Future-proof: Fully compatible with HHVM and PHP 7.

Getting started
---
To get started, add Enlighten as a Composer dependency to your project:

    composer require enlighten/framework

In the entry point (`index.php`) of your application, initialize and start Enlighten:

    $app = new Enlighten();
    $app->start();
    
You'll need to make sure that your web server redirects all requests you want to handle with Enlighten to this script. This code will initialize a blank application and process all incoming requests.

Next, you will want to define routes. Routes map an incoming request to an appropriate function or controller that can respond to it. It's easy to set up:

    $app->get('/articles/$name', function ($name) {
        // Triggered for all GET requests to /articles/*
        echo "You requested an article with this name: $name";
    });

**Check out the full documentation and quickstart guide at 
<https://enlighten.readthedocs.org/en/latest/>.**
