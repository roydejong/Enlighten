Enlighten Framework
===

**Enlighten is a simple, lean, high-performance PHP micro framework that acts as the foundation for your web application.**

This is a modern framework that doesn't get in your way: a low learning curve + zero configuration. Just the building blocks you need to accelerate your application development and simply *get shit done*. 

- Easy HTTP request and response management
- Razor fast routing with dynamic variables (DI)
- Form validation and on-the-fly generation
- MVC building blocks for a well-organized application
- Built for ease of use and performance
- Low on fat: small code base with no external dependencies

**Note: This is pre-release information, this does not reflect the current or even a planned final state of the project. This is, right now, just an experiment.**

Getting started
---
To get started, add Enlighten as a Composer dependency to your project:

    composer require roydejong/enlighten

In the entry point (`index.php`) of your application, initialize and start Enlighten:

    $app = new Enlighten();
    $app->start();