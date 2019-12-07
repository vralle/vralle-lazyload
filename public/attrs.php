<?php
/**
 * Attributes
 *
 * @package vralle-lazyload
 */

namespace VRalleLazyLoad;

use function array_intersect;
use function array_key_exists;
use function array_map;
use function apply_filters;
use function explode;
use function implode;
use function in_array;
use function strpos;
use function str_replace;

/**
 * Tag attributes handler
 *
 * @param array    $attrs    A list of tag attributes.
 * @param string   $tag_name HTML tag name.
 * @param null|int $id       Attachment ID if present or null.
 * @param mixed    $size     Size of image. Image size or array of width and
 *                           height values. Null if not preset.
 * @return array A list of tag attributes and their values.
 */
function attr_handler( $attrs, $tag_name, $id, $size ) {
	// Exit for Gaussholder.
	if ( array_key_exists( 'data-gaussholder', $attrs ) ) {
		return $attrs;
	}
	// Exit if ready to lazyload.
	if ( array_key_exists( 'data-srcset', $attrs ) || array_key_exists( 'data-src', $attrs ) ) {
		// set aspectratio for attachment attributes.
		$attrs = set_aspectratio( $attrs );
		return $attrs;
	}

	$class_names = array();
	if ( isset( $attrs['class'] ) ) {
		$class_names      = explode( ' ', $attrs['class'] );
		$skip_class_names = explode( ' ', get_option( 'skip_class_names' ) );
		if ( ! empty( array_intersect( $skip_class_names, $class_names ) ) ) {
			return $attrs;
		}
		if ( ! $id ) {
			$id = search_id_in_classes( $class_names );
		}
		if ( ! $size ) {
			$id = search_size_in_classes( $class_names );
		}
	}

	$have_src    = false;
	$placeholder = get_placeholder( $tag_name, $id, $size );

	if ( isset( $attrs['srcset'] ) && ! empty( $attrs['srcset'] ) ) {
		$attrs['data-srcset'] = $attrs['srcset'];
		$attrs['srcset']      = $placeholder;
		$have_src             = true;

		$attrs = set_sizes( $attrs );
	} elseif ( isset( $attrs['src'] ) && ! empty( $attrs['src'] ) ) {
		$attrs['data-src'] = $attrs['src'];
		$attrs['src']      = $placeholder;
		$have_src          = true;
		// Cleanup Dry Tags.
		unset( $attrs['sizes'] );
	}

	// Do lazyloaded, only if the image have src or srcset.
	if ( $have_src ) {
		$attrs = set_aspectratio( $attrs );
		$attrs = set_native_loading( $attrs );
		// Setup CSS-classes.
		$class_names[] = get_lazy_loading_class( $tag_name, $id, $size );
	}

	$attrs['class'] = implode( ' ', array_map( 'sanitize_html_class', $class_names ) );

	/**
	 * Filters the tag attributes
	 *
	 * @param array    $attrs HTML tag attributes.
	 * @param string   $tag   HTML tag name.
	 * @param null|int $id    Attachment ID if present or null.
	 * @param mixed    $size  Size of image. Image size or array of width and
	 *                        height values. Null if not preset.
	 */
	$attrs = apply_filters( 'vll_tag_attributes', $attrs, $tag_name, $id, $size );

	return $attrs;
}

/**
 * Retrieve the CSS class name used as a lazy loading trigger
 *
 * @param string   $tag_name   HTML tag name.
 * @param null|int $id         Attachment ID if present or null.
 * @param mixed    $size       Size of image. Image size or array of width and
 *                             height values. Null if not preset.
 * @return string The CSS class name.
 */
function get_lazy_loading_class( $tag_name, $id, $size ) {
	/**
	 * Filters the CSS class name
	 *
	 * @param string CSS Class Name.
	 * @param string   $tag_name   HTML tag name.
	 * @param null|int $id         Attachment ID if present or null.
	 * @param mixed    $size       Size of image. Image size or array of width and
	 *                             height values. Null if not preset.
	 */
	return apply_filters( 'vll_lazy_loading_class', 'lazyload', $tag_name, $id, $size );
}

/**
 * Retrieve the image that will be displayed before loading the original
 *
 * @param string   $tag_name Tag name.
 * @param null|int $id       Attachment ID if present or null.
 * @param mixed    $size     Size of image. Image size or array of width and
 *                           height values. Null if not preset.
 * @return string A link to image or base64 image.
 */
function get_placeholder( $tag_name, $id, $size ) {
	switch ( $tag_name ) {
		case 'iframe': // set valid src for embed tags.
			$placeholder = 'about:blank';
			break;
		default:
			$placeholder = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
			break;
	}

	/**
	 * Filters the image placeholder
	 *
	 * @param string   $placeholder Placeholder.
	 * @param string   $tag         HTML tag name.
	 * @param null|int $id          Attachment ID if present or null.
	 * @param mixed    $size        Size of image. Image size or array of width and
	 *                              height values. Null if not preset.
	 */
	return apply_filters( 'vll_placeholder', $placeholder, $tag_name, $id, $size );
}

/**
 * Search for attachment id in classes
 *
 * @param array $class_names A list of image classes.
 * @return null|int Attachment ID if present or null.
 */
function search_id_in_classes( $class_names ) {
	$id = null;
	foreach ( $class_names as $class_name ) {
		if ( 0 === strpos( $class_name, 'wp-image-' ) ) {
			$id = (int) str_replace( 'wp-image-', '', $class_name );
		}
	}

	return $id;
}

/**
 * Search for attachment size in classes
 *
 * @param array $class_names A list of image classes.
 * @return null|string Attachment size if present or null.
 */
function search_size_in_classes( $class_names ) {
	$size = null;
	foreach ( $class_names as $class_name ) {
		if ( 0 === strpos( $class_name, 'size-' ) ) {
			$size = str_replace( 'size-', '', $class_name );
		}
	}

	return $size;
}

/**
 * Set sizes attribute
 *
 * @param array $attrs A list of tag attributes.
 * @return array A list of tag attributes.
 */
function set_sizes( $attrs ) {
	if ( get_option( 'data-sizes' ) ) {
		$attrs['data-sizes'] = 'auto';
		unset( $attrs['sizes'] );
	}

	return $attrs;
}

/**
 * Set aspect ratio attribute
 *
 * @param array $attrs A list of tag attributes.
 * @return array A list of tag attributes.
 */
function set_aspectratio( $attrs ) {
	if ( ! isset( $attrs['data-aspectratio'] ) && get_option( 'aspectratio' ) ) {
		$width  = isset( $attrs['width'] ) ? absint( $attrs['width'] ) : null;
		$height = isset( $attrs['height'] ) ? absint( $attrs['height'] ) : null;
		if ( $width && 0 !== $height ) {
			$attrs['data-aspectratio'] = $width . '/' . $height;
		}
	}

	return $attrs;
}

/**
 * Set native lazy load attribute
 *
 * @param array $attrs A list of tag attributes.
 * @return array A list of tag attributes.
 */
function set_native_loading( $attrs ) {
	if ( ! isset( $attrs['loading'] ) && get_option( 'native-loading' ) ) {
		$attrs['loading'] = 'lazy';
	}

	return $attrs;
}
