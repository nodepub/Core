NodePub Core
============

The core components of NodePub CMS, built upon the Silex framework together with a number of Symfony components and other libraries.

This core library defines a number of Silex Service Providers that control multi-site management, the admin dashboard and admin routes, and the Extension Manager.

## Service Providers

* The CoreServiceProvider registers and bootstraps all of the necessary base Silex Service Providers (forms, logging, twig, etc.) as well as registering the other core NodePub Service Providers.
