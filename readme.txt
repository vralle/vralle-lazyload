=== vralle.lazyload ===
Contributors: vit-1
Tags: media, images, lazyload, performance, speed
Requires at least: 4.9
Tested up to: 5.5.0
Requires PHP: 5.6
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Brings lazySizes.js to WordPress.

== Description ==
vralle.lazyload uses lazysizes.js  - a fast (jank-free), SEO-friendly and self-initializing lazyloader for images (including responsive images), iframes and much more.

This is not only a lazy loading plugin, but also an image tag parser for WordPress. The plugin uses fast and safe image attribute processing. Supports responsive images.

Why?
* Very fast and secure code parsing
* Flexible for developers
* Easy to use

Implemented:
* Lazy loading images;
* Lazy loading Avatars;
* Supports responsive images with srcset attribute;
* Lazy loading iframe;
* Admin settings page;
* Exclude images by CSS-class or filter;
* Additional lazySizes.js extensions
* Supports native lazy load (lazysizes.js plugin);
* Support for responsive images in older browsers, like IE 10, 11 (picturefill.js)
* AMP-ready

== Installation ==
1. Upload `vralle-lazyload` to the Wordpress plugins directory
2. Activate the plugin through the \'Plugins\' menu in WordPress
3. Check out the settings page

== Changelog ==
- 1.0.2
  - WP 5.5 compatibility: Control native lazy loading by plugin settings.
  - Move Project requirements to Composer.
  - Move dependencies from `vendor` directory to `dist` for Composer compatibility
- 1.0.1
  - lazySizes v5.2.0
  - Tested with WP 5.4.0
- 1.0.0
  - Stable release
- 0.9.9
  - The plugin code is rewritten.
  - lazySizes v5.1.2
- 0.9.8
  - Extended aspectratio support
  - The Text Widget Support
  - Moving license from MIT to GPLv2+
  - Moving PHP Coding Standards from PSR-2 to WordPress
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
  - Expansion of security  - more escaping for admin page and options
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
