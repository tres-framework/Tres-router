# Tres router

This is the router package used for [Tres Framework][framework]. 
This is a stand-alone package, which means that it can also be used without the framework.

A router generally forwards you to something. The router takes the URI,
matches it with a list and takes action based on that.

This technique adds a degree of separation between the files used to generate a 
webpage and the URL that is presented to the world. An addition to that is not 
only that it is search engine friendly, but also that it's prettier.

![route example](_images/route-example.png "How routing could work")

## Requirements
- PHP 5.4 or greater.
- A web server with .htaccess support.
- rewrite_module for .htaccess.

## Documentation
[Documentation][documentation].

[framework]: https://github.com/tres-framework/Tres
[documentation]: https://github.com/tres-framework/docs/tree/master/routing
