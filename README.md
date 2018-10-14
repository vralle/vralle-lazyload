# vralle.lazyload
Brings [lazySizes.js](https://github.com/aFarkas/lazysizes) to WordPress.

* Tags: media, images, lazyload, performance, speed
* Tested up to: 4.9.8
* Stable tag: master
* License: [MIT License](LICENSE.txt)

Implemented:
  - Lazy loading Wordpress attachments;
  - Lazy loading of embedded images in the post content;
  - Lazy loading the Avatar;
  - Full support responsive images with srcset attribute;
  - Lazy loading iframe, embed, object and video tags;
  - Admin settings page;
  - Exclude images by CSS-class
  - Fine tuning lazySizes.js
  - Additional lazySizes.js extensions
  - Template Tags for background

## Installation

1. Install [github-updater](https://github.com/afragen/github-updater) by downloading the latest zip [here](https://github.com/afragen/github-updater/releases). We rely on this plugin for updating vralle.lazyload directly from this git repo.
2. Install vralle.lazyload by downloading the latest zip [here](https://github.com/vralle/vralle-lazyload/releases). Both github-updater and vralle.lazyload will now download their own updates automatically, so you will never need to go through that tedious zip downloading again.
3. Check out the settings page to fine-tune your settings.

## How to

### Image that replaces the original
```
add_filter('vralle_lazyload_image_placeholder', 'custom_placeholder');
function custom_placeholder($placeholder) {
    $placeholder = 'https://url.to.image';
    return $placeholder;
}
```
`$placeholder` - image url or base64 string,
default: 1px*1px `data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==`

### Exclude images

By CSS-class - the plugin options page.

And/or filter `do_vralle_lazyload`:
```
add_filter('do_vralle_lazyload', 'my_handler');
function my_handler()
{
  if (is_category(1)) {
    return false;
  }

  return true;
}
```

### Load lazySizes extensions
```
add_filter('lazysizes_plugins', $plugin_list);
```
`$plugin_list` - array list of plugin names

Example:
```
add_filter('lazysizes_plugins', 'my_lazysizes_plugins_list');
function my_lazysizes_plugins_list($plugins)
{
    $plugins[] = 'bgset';
    return $plugins;
}
```

### Background images
To work with background images, you can use the `vr_get_image_attr(thumbnail_id, size)`, but you need to edit the template code.
Example:
```
<?php if (has_post_thumbnail()) : ?>
      <?php
      $thumbnail = vr_get_image_attr(get_post_thumbnail_id($post->ID), 'large');
      // Calculate aspect ratio: h / w * 100%.
      $ratio = $thumbnail['height'] / $thumbnail['width'] * 100;
      ?>
      <div class="panel-image lazyload" <?php echo $thumbnail['bg-data']; ?>>
          <div class="panel-image-prop" style="padding-top: <?php echo esc_attr($ratio); ?>%"></div>
      </div><!-- .panel-image -->
  <?php endif; ?>
```
How this works can be found in the file `app\template-tags.php`
Do not forget to add the required plugin.

## Changelog
- 0.9.1
 - Update dependencies. lazySizes v4.1.4
- 0.9.0
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

Copyright 2017-2018 the Authors. This project is licensed under the terms of the [MIT License](LICENSE.txt).

**Free Software, Hell Yeah!**
