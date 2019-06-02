=== vralle.lazyload ===
Contributors: V.Ralle
Tags: media, images, lazyload, performance, speed
Requires at least: 4.4
Tested up to: 5.2.0
Requires PHP: 5.6
Stable tag: 0.9.7
License: MIT
License URI: https://license.uri

Brings lazySizes.js to WordPress.

== Description ==
vralle.lazyload uses lazysizes.js - a fast (jank-free), SEO-friendly and self-initializing lazyloader for images (including responsive images picture/srcset), iframes and much more.

Implemented:
* Lazy loading Wordpress attachments;
* Lazy loading of embedded images in the post content;
* Lazy loading the Avatar;
* Full support responsive images with srcset attribute;
* Lazy loading iframe, embed, object and video tags;
* Admin settings page;
* Exclude images by CSS-class
* Fine tuning lazySizes.js
* Additional lazySizes.js extensions
* Support for responsive images in older browsers, like IE 10, 11
* Template Tags for backgrounds
* Handler skips AMP pages

== Installation ==
1. Upload `vralle-lazyload` to the Wordpress plugins directory
2. Activate the plugin through the \'Plugins\' menu in WordPress
3. Check out the settings page to fine-tune your settings

== Changelog ==
- 0.9.7
  - lazySizes v5.1.0
  - Draft of aspectratio plugin support
- 0.9.6
  - Fixed a data-sizes attribute
- 0.9.5
  - Refactoring the plugin
  - Rename `vralle_lazyload_lazy_class` filter to `vralle_lazyload_css_class`
  - Rename `exclude_class` option to `css_exception`
  - AMP Support. Handler skips AMP pages
  - lazySizes v4.1.8
- 0.9.4:
  - Fix Github URI
- 0.9.3:
  - Add picturefill.js
- 0.9.2:
  - Update dependencies. lazySizes v4.1.5
- 0.9.1:
  - Update dependencies. lazySizes v4.1.4
- 0.9.0:
  - Refactoring the plugin
- 0.8.2:
  - lazySizes v4.1.0
  - Added parent-fit extension settings
  - iframe, embed, object, video tags added to the handler
  - Expansion of security - more escaping for admin page and options
  - Performance optimization
  - Internationalization fix
- 0.8.0:
  - lazySizes v4.0.2
  - updated options page
  - loading extensions through a filter only
  - Now PSR-2
- 0.7.0:
  - Move vendor from git to npm. lazySizes v4.0.1
  - Added .pot
- 0.6.0:
  - Added content images support
  - Added avatar support
  - Added template tag for background images
  - Enhanced options
- 0.5.0:
  - Initial
