# vralle.lazyload
Brings [lazySizes.js](https://github.com/aFarkas/lazysizes) to WordPress.
This is not only a lazy loading plugin, but also an image tag parser for WordPress. The plugin uses fast and safe image attribute processing. Supports responsive images.

* Contributors: V.Ralle
* Tags: media, images, lazyload, performance, speed
* Requires at least: 4.9
* Tested up to: 5.3.0
* Requires PHP: 5.6
* Stable tag: [master](https://github.com/vralle/vralle-lazyload/releases/latest)
* License: [GPL 2.0 or later](LICENSE.txt)

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

## Installation

1. Install [github-updater](https://github.com/afragen/github-updater) by downloading the latest zip [here](https://github.com/afragen/github-updater/releases). We rely on this plugin for updating vralle.lazyload directly from this git repo.
2. Install vralle.lazyload by downloading the latest zip [here](https://github.com/vralle/vralle-lazyload/releases). Both github-updater and vralle.lazyload will now download their own updates automatically, so you will never need to go through that tedious zip downloading again.
3. Check out the settings page to fine-tune your settings.

## Known issues
### Layout thrashing
The layout may be distorted until the images are loaded. After each image is loaded, the page is recalculated, which is called [Layout thrashing](https://kellegous.com/j/2013/01/26/layout-performance/).
Several solutions may help to avoid such behavior.
For example, place an image in a container and determine the aspect ratio.
```html
<div class="img-container">
  <div class="img-sizer">
    <img
      class="content-img lazyload"
      src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
      data-srcset="http://lorempixel.com/400/200/people/1/ 400w, http://lorempixel.com/600/300/people/1/ 600w, http://lorempixel.com/800/400/people/1/ 800w"
      data-sizes="auto"
    />
  </div>
</div>
```
```css
.img-container {
  position: relative;
  display: block;
  width: 100%;
  height: auto;
  overflow: hidden;
}
.img-sizer {
  position: relative;
  z-index: 1;
  width: 100%;
  height: 0;
  padding: 0;
  padding-bottom: 56.25%; // Aspect ratio (16:9)
  margin: 0;
  overflow: hidden;
}
.content-img {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: auto;
  max-width: 100%;
  padding: 0;
  margin: 0;
}
```

The plugin cannot provide all layout options for images, therefore we recommend to provide support for lazy loading in the active theme.

If you cannot change the layout, you can use [aspectratio extension](https://github.com/aFarkas/lazysizes/tree/gh-pages/plugins/aspectratio).

## Changelog
- 1.0.0
  - Stable release
- 0.9.9
  - The plugin code is rewritten. Need feedback.
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

## Copyright and license

Copyright 2017-2018 the Authors.

This project is licensed under the terms of the [GPL 2.0 or later](LICENSE.txt).

LazySizes licensed under the [MIT license](https://github.com/aFarkas/lazysizes/blob/gh-pages/LICENSE).


**Free Software, Hell Yeah!**
