NodePub Core
============

The core components of NodePub CMS, built upon the Silex framework together with a number of Symfony components and other libraries.

This library defines a number of Silex Service Providers that control multi-site management, an admin dashboard and admin routes, the Extension Container, and core extensions.

## Service Providers

* The CoreServiceProvider registers and bootstraps all of the necessary base Silex Service Providers (forms, logging, twig, etc.) as well as registering the other core NodePub Service Providers.

## Extensions

An extension can provide any or all of the following:

* toolbar items
* block types
* twig extensions
* snippets that will be inserted into the rendered HTML
* snippets that will be inserted into the page when logged in.

### Core Extensions

Several pieces of core functionality are written as extensions, with the exception that they can't be disabled or removed.