=== VRALLE.Lazyload ===
Contributors: V.Ralle
Tags: media, images, lazyload, performance, speed
Requires at least: 4.9
Requires PHP: 7.0
Stable tag: 0.9.0
Tested up to: 4.9.8

Brings lazySizes.js to WordPress.

== Description ==

vralle.lazyload uses lazysizes.js - a fast (jank-free), SEO-friendly and self-initializing lazyloader for images (including responsive images picture/srcset), iframes and much more.

== Installation ==

1. Upload `vralle-lazyload` to the Wordpress plugins directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Check out the settings page to fine-tune your settings

== Changelog ==

= 0.9.0
  * Refactoring the plugin

= 0.8.2 =
  * lazySizes v4.1.0
  * Added parent-fit extension settings
  * iframe, embed, object, video tags added to the handler
  * Expansion of security - more escaping for admin page and options
  * Performance optimization
  * Internationalization fix

= 0.8.0 =
  * Now PSR-2
  * lazysizes v4.0.2
  * updated settings page
  * loading plug-ins through a filter only

= 0.7.0 =
* Move vendor from git to npm. lazysizes v4.0.1
* Add .pot

= 0.6.0 =
* Added content image support
* Added avatar support
* Added template tag for background images
* Enhanced settings

= 0.5.0 =
* Initial.
