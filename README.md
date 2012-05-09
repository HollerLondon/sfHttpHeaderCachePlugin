sfHttpHeaderCachePlugin
=======================

Goal
----

This plugin aims to sit on top of Symfony's built-in caching configuration to provide a means to configure reverse proxy (e.g. nginx or Varnish) caching through the use of sending good caching headers with the response. It also provides an alternative cache for template fragments (partials and components) using existing caching mechanisms native to Symfony or compatible with Symfony, like sfApcCache, for example.

A word of warning
-----------------

With a caching reverse proxy in place (or even a proxy at an ISP level), this plugin will cache the whole response when with_layout is set to true in your factories.yml. This may result in unexpected results if you rely on the user session, so use it with caution, as you could end up spewing other people's data out for all other users infront of your caching reverse proxy (or indeed, behind a ISP's proxy).

Configuration
-------------

Firstly, install the plugin in the usual way (either on with the cli or using svn:externals).

Next, set your factories.yml to use the sfHttpHeaderCache class provided by the plugin:

    all:
      view_cache:
        class:    sfHttpHeaderCache
        param:
          headers:
            Expires:        "%EXPIRE_TIME%"
            Last-Modified:  "%LAST_MODIFIED%"
            Cache-Control:  "public, max_age=%LIFETIME%"
            ETag:           '%ETAG%'

Then, make sure settings.yml is set to enable caching:

    prod:
      cache: true

For all actions that you'd like to cache, you should then configure your project's, app's or modules's cache.yml to do so:

    default:
      enabled: true
      with_layout: true # Headers are only sent for caches with layout
      lifetime: 3600

The nature of this plugin is that it only caches whole-page responses with the layout and all. If you'd like to use the partial cache as well, you can embellish the factories.yml with an alternative cache like so:

    all:
      view_cache:
        class:    sfHttpHeaderCache
        param:
          headers:
            Expires:        "%EXPIRE_TIME%"
            Last-Modified:  "%LAST_MODIFIED%"
            Cache-Control:  "public, max_age=%LIFETIME%"
            ETag:           '%ETAG%'
          alt:
            cache: 
              # This could be any of the Symfony API caches, defaults to sfNoCache
              class: sfMemcacheCache
              # Any valid options for your selected alt-cache
              param:
                servers: # Array of servers
                  localhost:
                    host: 127.0.0.1

Credits
-------

This plugin started life as [a post on Symfony Snippets by Christian Schaefer](http://snippets.symfony-project.org/snippet/365).