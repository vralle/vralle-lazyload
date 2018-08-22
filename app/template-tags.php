<?php
/**
 * Custom template tags
 * @package    vralle-lazyload
 * @subpackage vralle-lazyload/app
 */

/**
 * Get a image data
 * @param  integer      $attachment_id    Image attachment ID.
 * @param  string|array $size Image size  Accepts any valid image size, or an array of width and height values in pixels (in that order).
 *                                         Default value: 'thumbnail'
 * @return  array       A List of image data
 */
function vr_get_image_attr($attachment_id, $size = 'thumbnail')
{
    $attr = array();

    $img = wp_get_attachment_image_src($attachment_id, $size);
    $attr['src'] = $img[0];
    $attr['width'] = $img[1];
    $attr['height'] = $img[2];
    $attr['bg-data'] = sprintf('data-bg="%s"', $img[0]);

    // Generate 'srcset' and 'sizes'
    $image_meta = wp_get_attachment_metadata($attachment_id);

    if (is_array($image_meta)) {
        $size_array = array(absint($attr['width']), absint($attr['height']));
        $srcset = wp_calculate_image_srcset($size_array, $attr['src'], $image_meta, $attachment_id);
        $sizes = wp_calculate_image_sizes($size_array, $attr['src'], $image_meta, $attachment_id);

        if ($srcset) {
            $attr['srcset'] = $srcset;
            if ($sizes || !empty($attr['sizes'])) {
                $attr['sizes'] = $sizes;
            } else {
                $attr['sizes'] = '';
            }
            $attr['bg-data'] = sprintf('data-bgset="%s" data-sizes="auto"', $srcset);
        }
    }

    return $attr;
}
