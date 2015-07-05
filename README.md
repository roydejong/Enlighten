Enlighten Framework
===

**Note: Pre-release information, this does not reflect the current or final state of the project**.

Why?
---
Enlighten is a simple, lean, high-performance framework for building web applications using PHP.

The goal: a framework with a low learning curve that helps you **get shit done** without getting in your way.

Do not expect 

What?
---
Enlighten offers the following features:

- Model / View / Controller (MVC) architecture
- Modular project organisation
- Lightweight, dynamic routing with Dependency Injection (DI)
- HTTP utilities for reading Responses and writing Requests

Getting started
---
To get started, add Enlighten as a Composer dependency to your project:

    composer require roydejong/enlighten

In the entry point (`index.php`) of your application, initialize and start Enlighten:

    $app = new Enlighten();
    $app->start();