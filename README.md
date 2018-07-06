# Vralle.Lazyload
The plugin uses [lazysizes.js](https://github.com/aFarkas/lazysizes) - High performance and SEO friendly lazy loader for images, iframes and more.

* Tags: media, images, iframe, lazyload, performance, speed
* Tested up to: 4.9.7
* Stable tag: master
* License: [MIT License](LICENSE.txt)

Implemented:
  - Image attachments support
  - Content image support
  - Support images in the Custom Header
  - Avatar support
  - Responsive images support
  - Exclude images by CSS-class
  - Settings page
  - Selecting additional lazysizes.js plugins
  - Template Tags for background

The plugin is in active development.

## Installation

1. Install [github-updater](https://github.com/afragen/github-updater) by downloading the latest zip [here](https://github.com/vralle/vralle-lazyload/releases). We rely on this plugin for updating Vralle.Lazyload directly from this git repo.
2. Install Vralle.Lazyload by downloading the latest zip [here](). Both github-updater and Vralle.Lazyload will now download their own updates automatically, so you will never need to go through that tedious zip downloading again.
3. Access the Vralle.Lazyload menu option under Tools.

## How to

### Image that replaces the original
```
add_filter('vralle_lazyload_image_placeholder', $my_image);
```
`$my_image` - url or base64 image string,
default: 1px*1px `data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==`

### Exclude images

By CSS-class on the plugin settings page.

And/or add filter:
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

### Load plugins
```
add_filter('lazysizes_plugins', $plugin_list);
```
`$plugin_list` - array list of plugin names

Example:
```
add_filter('lazysizes_plugins', 'lazySizesPlugins');
function lazySizesPlugins()
{
    return array(
        'bgset',
    );
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
How this works can be found in the file app\template-tags.php
Do not forget to add the required plugin.

## Changelog

- current:
  - Added iframes support
- 0.8.0:
  - lazysizes v.4.0.2
  - updated settings page
  - loading plug-ins through a filter only
  - Now PSR-2
- 0.7.0:
  - Move vendor from git to npm. lazysizes v.4.0.1
  - Add .pot
- 0.6.0:
  - Added content images support
  - Added avatar support
  - Added template tag for background images
  - Enhanced settings
- 0.5.0:
  - Initial

## Development

Want to contribute? Great!

## Todos

## Copyright and license

Copyright 2017-2018 the Authors. This project is licensed under the terms of the [MIT License](LICENSE.txt) license.

**Free Software, Hell Yeah!**
