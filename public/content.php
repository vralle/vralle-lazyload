<?php
/**
 * Handlers
 *
 * @package vralle-lazyload
 */

namespace VRalleLazyLoad;

use function preg_replace_callback;
use function wp_kses_hair;
use function wp_allowed_protocols;
use function is_int;
use function esc_attr;
use function sprintf;
use function str_replace;
use function implode;
use function array_map;
use function array_merge;
use function add_filter;

/**
 * Search HTML tags in content and manipulate tag attributes
 *
 * @param string   $html      Content to search for tags.
 * @param array    $tag_names tag names for search.
 * @param null|int $id        Attachment ID if present or null.
 * @param mixed    $size      Size of image. Image size or array of width and
 *                            height values. Null if not preset.
 * @return string Content with tags filtered out.
 */
function content_handler( $html, $tag_names, $id, $size ) {
	$pattern = get_tag_regex( $tag_names );

	return preg_replace_callback(
		"/$pattern/i",
		function ( $m ) use ( $id, $size ) {
			$tag            = $m[0];
			$tag_name       = $m[1];
			$kses_hair_data = wp_kses_hair( $m[2], wp_allowed_protocols() );
			$attrs_in_arr   = flatten_kses_hair_data( $kses_hair_data );
			$attrs_out_arr  = attr_handler( $attrs_in_arr, $tag_name, $id, $size );

			if ( $attrs_out_arr !== $attrs_in_arr ) {
				$attrs_out = '';
				foreach ( $attrs_out_arr as $key => $value ) {
					if ( is_int( $key ) ) {
						$attrs_out .= ' ' . esc_attr( $value );
					} else {
						$attrs_out .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
					}
				}
				$tag = str_replace( $m[2], $attrs_out, $tag );
			}

			return $tag;
		},
		$html
	);
}

/**
 * Retrieve the html tag regular expression
 *
 * @param array $tag_names A list of tag names to find.
 * @return string The html tag search regular expression.
 */
function get_tag_regex( $tag_names ) {
	$names = implode( '|', array_map( 'preg_quote', $tag_names ) );

	// phpcs:disable Squiz.Strings.ConcatenationSpacing.PaddingFound
	return '<\s*'            // Opening tag.
	. "($names)"              // Tag name.
	. '('
	.     '[^>\\/]*'         // Not a closing tag or forward slash.
	.     '(?:'
	.         '\\/(?!>)'     // A forward slash not followed by a closing tag.
	.         '[^>\\/]*'     // Not a closing tag or forward slash.
	.     ')*?'
	. ')'
	. '\\/?>';               // Self closing tag.
	// phpcs:enable
}

/**
 * Flattens an attribute list into key value pairs.
 *
 * @param array $attrs Array of attributes.
 * @return array Flattened attributes as $attr => $attr_value pairs.
 */
function flatten_kses_hair_data( $attrs ) {
	$flattened_attrs = array();
	foreach ( $attrs as $attr ) {
		$flattened_attrs[ $attr['name'] ] = $attr['value'];
	}
	return $flattened_attrs;
}
