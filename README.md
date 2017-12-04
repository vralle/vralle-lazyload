# Vralle.Lazyload
The plugin uses [lazysizes.js](https://github.com/aFarkas/lazysizes) - High performance and SEO friendly lazy loader for images, iframes and more.

The plugin is in active development.

Implemented:
  - Wordpress image support
  - Content image support
  - Avatar support
  - Responsive image support
  - Exclude images by CSS-class
  - Settings page
  - Selecting additional lazysizes.js plugins
  - Template Tags for background images

## Requirements

When writing the code, I used PHP 7.1 and Wordpress 4.9. The plugin has not been tested with other versions of PHP or WordPress.

## Installation

```sh
$ git clone --recursive https://github.com/vralle/VRALLE.Lazyload.git wordpress/wp-content/plugins/vralle-lazyload
```
This will add the original lazysizes.js repository.

Or you can add lazysizes.js later:

```sh
$ git clone https://github.com/vralle/VRALLE.Lazyload.git wordpress/wp-content/plugins/vralle-lazyload
$ cd .\vralle-lazyload\
$ git submodule init
$ git submodule update
```

Read more: [https://git-scm.com/book/en/v2/Git-Tools-Submodules](https://git-scm.com/book/en/v2/Git-Tools-Submodules)

Then:
* Activate The Plugin
* Check the settings on the plugin settings page

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


## Changelog


- 0.6.0:
  - Added content image support
  - Added avatar support
  - Added template tag for background images
  - Enhanced settings
- 0.5.0:
  - Initial stable version. Only Wordpress image support

## Development

Want to contribute? Great!

## Todos

  - iframe support
  - Widget image support
  - Enhanced settings

## Copyright and license

Copyright 2017 the Authors. This project is licensed under the terms of the [MIT License](LICENSE.txt).

**Free Software, Hell Yeah!**
