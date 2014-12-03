# Tres router

This is the router package used for [Tres Framework][framework]. 
This is a stand-alone package, which means that it can also be used without the framework.

A router generally forwards you to something. In this case, we take the URI, match it with a 
list and do something based on that. What you want to do with the routes is up to you.

This technique adds a degree of separation between the files used to generate a webpage and the 
URL that is presented to the world. An addition to that is not only that it is search engine friendly,
but also that it's prettier for humans like you and me.

## Requirements
- PHP 5.4 or greater.
- A web server with .htaccess support.
- rewrite_module for .htaccess.

## Documentation
For documentation, [click here][documentation].

[framework]: https://github.com/tres-framework/Tres
[documentation]: https://github.com/tres-framework/docs/blob/master/routing.md
