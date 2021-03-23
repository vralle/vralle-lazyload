# vralle.lazyload
Brings [lazySizes.js](https://github.com/aFarkas/lazysizes) to WordPress.
This is not only a lazy loading plugin, but also an image tag parser for WordPress. The plugin uses fast and safe image attribute processing. Supports responsive images.

-   Contributors: V.Ralle
-   Tags: media, images, lazyload, performance, speed
-   Requires at least: 4.9
-   Tested up to: 5.7.0
-   Requires PHP: 7.1
-   Stable tag: [master](https://github.com/vralle/vralle-lazyload/releases/latest)
-   License: [GPL 2.0 or later](LICENSE.txt)

Why?
-   Very fast and secure code parsing
-   Flexible for developers
-   Easy to use

Implemented:
-   Lazy loading images;
-   Supports avatars;
-   Supports responsive images with srcset attribute;
-   Lazy loading iframe;
-   Admin settings page;
-   Additional lazySizes.js extensions;
-   Supports native lazy loading of images (lazysizes.js plugin);
-   Support for responsive images in older browsers, like IE 10, 11 (picturefill.js);
-   Compatible with AMP plugin (amp-wp);
## Installation
### Manual installation
1. Upload the entire `vralle-lazyload` folder to the `/wp-content/plugins/` directory.
2. Visit **Plugins**.
3. Activate the vralle.lazyload plugin.
### Automatic update
1. Install [github-updater](https://github.com/afragen/github-updater) by downloading the latest zip [here](https://github.com/afragen/github-updater/releases). We rely on this plugin for updating vralle.lazyload directly from this git repo.
1. Install vralle.lazyload by downloading the latest zip [here](https://github.com/vralle/vralle-lazyload/releases). Both github-updater and vralle.lazyload will now download their own updates automatically, so you will never need to go through that tedious zip downloading again.
1. Check out the settings page to fine-tune your settings.
## How do I use it?
Select options on the plugin settings page.

You can also install [vralle.lqip](https://github.com/vralle/vralle-lqip) that adds Low Quality Image Placeholder. vralle.lqip also demonstrates how to use the API of vralle.lazyload.
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

## Hooks
```php
/**
 * Filters the plugin settings configuration
 *
 * @param array $config The plugin settings configuration.
 */
apply_filters( 'vll_plugin_config', $config );
```
```php
/**
 * Filters capabilities of the plugin settings page.
 */
apply_filters( 'vll_settings_capability', 'manage_options' );
```
```php
/**
 * Filters cancellation of all plugin handlers
 *
 * @param boolean
 */
apply_filters( 'do_vralle_lazyload', true )
```
```php
/**
 * Filters whether to add the lazy load attributes to the specified tag
 *
 * @param bool     $lazyload Determines whether to add lazy load attributes.
 * @param array    $attrs    A list of tag attributes.
 * @param string   $tag_name HTML tag name.
 * @param null|int $id       Attachment ID or null.
 * @param mixed    $size     Size of image. Size name or array of width and
 *                           height values. Null if not preset.
 */
apply_filters( 'vll_lazyload_element', true, $attrs, $tag_name, $id, $size );
```
```php
/**
 * Filters the placeholder
 *
 * @param string   $placeholder Image placeholder.
 * @param string   $tag         HTML tag name.
 * @param null|int $id          Attachment ID or null.
 * @param mixed    $size        Image size. Size name or array of width and height values.
 *                              Null if not present.
 */
apply_filters( 'vll_placeholder', $placeholder, $tag_name, $id, $size );
```
Default placeholder: `data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==`
```php
/**
 * Filters the CSS class name for lazy loading elements
 *
 * @param string   $class_name CSS Class Name.
 * @param string   $tag_name   HTML tag name.
 * @param null|int $id         Attachment ID or null.
 * @param mixed    $size       Size of image. Size name or array of width and
 *                             height values. Null if not present.
 */
apply_filters( 'vll_lazy_loading_class', 'lazyload', $tag_name, $id, $size );
```
```php
/**
 * Filters html tag attributes
 *
 * @param array    $attrs    HTML tag attributes.
 * @param string   $tag_name HTML tag name.
 * @param null|int $id       Attachment ID if present or null.
 * @param mixed    $size     Size of image. Size name or array of width and
 *                           height values. Null if not preset.
 */
apply_filters( 'vll_tag_attributes', $attrs, $tag_name, $id, $size );
```
```php
/**
 * Filters the list of lazysizes.js plugins to load
 *
 * @param array $load List of plugin names.
 */
apply_filters( 'vll_lazysizes_plugins', $load );
```
All possible plugins are located in the plugin directory: `vralle-lazyload/dist/lazysizes/plugins`
## Changelog
-   1.1.0
    -   Code transition to OOP
    -   Performance improvement
    -   Fixed bug on uninstall the plugin
	-   Added support for Core blocks
	-   Added 'vll_lazyload_element' hook
    -   Requires PHP: v7.1+
    -   Lazisizes.js v5.3.0
-   1.0.2
    -   WP 5.5 compatibility: Control native lazy loading by plugin settings.
    -   Move Project requirements to Composer.
    -   Move dependencies from `vendor` directory to `dist` for Composer compatibility
-   1.0.1
    -   lazySizes v5.2.0
    -   Tested with WP 5.4.0
-   1.0.0
    -   Stable release

## Copyright and license
Copyright 2017-2021 the Authors.

This project is licensed under the terms of the [GPL 2.0 or later](LICENSE.txt).

LazySizes licensed under the [MIT license](https://github.com/aFarkas/lazysizes/blob/gh-pages/LICENSE).

**Free Software, Hell Yeah!**
