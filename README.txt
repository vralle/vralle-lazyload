=== VRALLE.Lazyload ===
Contributors: Vitaliy Ralle
Tags: lazy load, lazy loading, images, iframes, optimize, performance
Requires at least: 4.9
Requires PHP: 7.0
Stable tag: 0.8.0
Tested up to: 4.9.4

Lazy loading images to speed up loading pages and reduce the load on the server. Images are loaded when they get to the screen. Uses lazysizes.js

== Description ==
VRALLE.Lazyload uses lazysizes.js - a fast (jank-free), SEO-friendly and self-initializing lazyloader for images (including responsive images picture/srcset), iframes and much more.
The plugin is in active development. The current version only works with Wordpress images, but already has several settings, including the ability to exclude images by  CSS classes.

== Installation ==

1. Upload `vralle-lazyload` to the Wordpress plugins directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Check the settings on the plugin settings page

== Changelog ==

= 0.8.0 =
  * Now PSR-2
  * lazysizes v.4.0.2
  * updated settings page
  * loading plug-ins through a filter only

= 0.7.0 =
* Move vendor from git to npm. lazysizes v.4.0.1
* Add .pot

= 0.6.0 =
* Added content image support
* Added avatar support
* Added template tag for background images
* Enhanced settings

= 0.5.0 =
* Initial.
