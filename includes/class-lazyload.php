<?php
/**
 * Class Lazyload - HTML Tag Parser and Lazy Load Attribute Handler
 *
 * PHP version 7.1
 *
 * @package vralle-lazyload
 * @subpackage vralle-lazyload/includes
 * @author V.Ralle <email4vit@gmail.com>
 * @copyright Copyright (c) 2021, V.Ralle
 * @license https://www.gnu.org/licenses/gpl-2.0.html
 * @since vralle-lazyload v1.1.0
 */

namespace VRalleLazyLoad;

\defined( 'ABSPATH' ) || exit;

use function \apply_filters;
use function \array_column;
use function \array_map;
use function \esc_attr;
use function \get_option;
use function \implode;
use function \is_int;
use function \preg_replace_callback;
use function \rtrim;
use function \sprintf;
use function \str_replace;
use function \strpos;
use function \wp_allowed_protocols;
use function \wp_kses_hair;

if ( ! class_exists( __NAMESPACE__ . '\\Lazyload' ) ) :

	/**
	 * HTML Tag Parser and Lazy Load Attribute Handler
	 */
	class Lazyload extends BaseSettings {

		/**
		 * Looks for HTML tags in the content and prepares the attributes
		 *
		 * @param string   $html Content to search for tags.
		 * @param array    $tags Tag names for search.
		 * @param null|int $id   Attachment ID or null.
		 * @param mixed    $size Size of image. Size name or array of width and
		 *                       height values. Null if not present.
		 *
		 * @return string Content with tags filtered out.
		 */
		public static function do_content( $html, $tags, $id = null, $size = null ) {
			$names = implode( '|', array_map( 'preg_quote', $tags ) );

			return preg_replace_callback(
				"/<\s*($names)([^>\/]*(?:\/(?!>)[^>\/]*)*?)\/?>/i",
				function ( $m ) use ( $id, $size ) {
					$tag_name = $m[1];

					$attrs_parsed  = wp_kses_hair( rtrim( $m[2], '/' ), wp_allowed_protocols() );
					$attrs_flatten = array_column( $attrs_parsed, 'value', 'name' );
					$attrs_ready   = self::do_attrs( $attrs_flatten, $tag_name, $id, $size );

					$attrs = '';
					foreach ( $attrs_ready as $key => $value ) {
						if ( is_int( $key ) ) {
							$attrs .= ' ' . esc_attr( $value );
						} else {
							$attrs .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
						}
					}

					$tag = str_replace( $m[2], $attrs, $m[0] );

					return $tag;
				},
				$html
			);
		}

		/**
		 * HTML tag attribute preparation
		 *
		 * @param array    $attrs    A list of tag attributes.
		 * @param string   $tag_name HTML tag name.
		 * @param null|int $id       Attachment ID or null.
		 * @param mixed    $size     Size of image. Size name or array of width and
		 *                           height values. Null if not preset.
		 *
		 * @return array A list of tag attributes and their values.
		 */
		public static function do_attrs( $attrs, $tag_name, $id = null, $size = null ): array {
			// Exit if ready to lazy load.
			if ( isset( $attrs['data-srcset'] ) || isset( $attrs['data-src'] ) ) {
				// Set aspect ratio for attachment.
				$attrs = self::set_aspectratio( $attrs );
				return $attrs;
			}

			$class_names = array();
			if ( isset( $attrs['class'] ) ) {
				$class_names = explode( ' ', $attrs['class'] );
				$exclude_css = isset( self::$options['skip_class_names'] ) ? explode( ' ', self::$options['skip_class_names'] ) : array();
				if ( ! empty( array_intersect( $exclude_css, $class_names ) ) ) {
					return $attrs;
				}
				if ( ! $id ) {
					$id = self::search_id_in_classes( $class_names );
				}
				if ( ! $size ) {
					$size = self::search_size_in_classes( $class_names );
				}
			}

			/**
			 * Filters whether to add the lazy load attributes to the specified tag
			 *
			 * @since 1.1.0
			 *
			 * @param bool     $do       Determines whether to add lazy load attributes.
			 * @param array    $attrs    A list of tag attributes.
			 * @param string   $tag_name HTML tag name.
			 * @param null|int $id       Attachment ID or null.
			 * @param mixed    $size     Size of image. Size name or array of width and
			 *                           height values. Null if not preset.
			 */
			$lazyload = apply_filters( 'vll_lazyload_element', true, $attrs, $tag_name, $id, $size );

			if ( ! $lazyload ) {
				return $attrs;
			}

			$have_src = false;

			$placeholder = self::get_placeholder( $tag_name, $id, $size );

			if ( ! empty( $attrs['srcset'] ) ) {

				$attrs['data-srcset'] = $attrs['srcset'];
				$attrs['srcset']      = $placeholder;

				$have_src = true;
				$attrs    = self::set_sizes( $attrs );

			} elseif ( ! empty( $attrs['src'] ) ) {
				$attrs['data-src'] = $attrs['src'];
				$attrs['src']      = $placeholder;

				$have_src = true;
				// Cleanup Dry Tags.
				unset( $attrs['sizes'] );
			}

			// Lazy load only if the image has src or srcset.
			if ( $have_src ) {
				$attrs = self::set_aspectratio( $attrs );
				$attrs = self::set_native_loading( $attrs );
				// Setup CSS-classes.
				$class_names[] = self::get_lazy_load_class( $tag_name, $id, $size );
			}

			$attrs['class'] = implode( ' ', array_map( 'sanitize_html_class', $class_names ) );

			/**
			 * Filters the tag attributes
			 *
			 * @param array    $attrs HTML tag attributes.
			 * @param string   $tag_name   HTML tag name.
			 * @param null|int $id         Attachment ID if present or null.
			 * @param mixed    $size       Size of image. Size name or array of width and
			 *                             height values. Null if not present.
			 */
			$attrs = (array) apply_filters( 'vll_tag_attributes', $attrs, $tag_name, $id, $size );

			return $attrs;
		}

		/**
		 * Retrieve the image that will be displayed before loading the original
		 *
		 * @param string   $tag_name HTML tag name.
		 * @param null|int $id       Attachment ID or null.
		 * @param mixed    $size     Size of image. Size name or array of width and
		 *                           height values. Null if not preset.
		 *
		 * @return string A link to image or base64 image.
		 */
		public static function get_placeholder( $tag_name, $id, $size ): string {
			switch ( $tag_name ) {
				case 'iframe': // set valid src for embed tags.
					$placeholder = 'about:blank';
					break;
				default:
					$placeholder = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
					break;
			}

			/**
			 * Filters the placeholder
			 *
			 * @param string   $placeholder Image placeholder.
			 * @param string   $tag         HTML tag name.
			 * @param null|int $id          Attachment ID or null.
			 * @param mixed    $size        Image size. Size name or array of width and height values.
			 *                              Null if not present.
			 */
			return apply_filters( 'vll_placeholder', $placeholder, $tag_name, $id, $size );
		}

		/**
		 * Retrieve the CSS class name used as a lazy load trigger
		 *
		 * @param string   $tag_name   HTML tag name.
		 * @param null|int $id         Attachment ID or null.
		 * @param mixed    $size       Size of image. Size name or array of width and
		 *                             height values. Null if not present.
		 *
		 * @return string The CSS class name.
		 */
		public static function get_lazy_load_class( $tag_name, $id, $size ): string {
			/**
			 * Filters the CSS class name for lazy loading elements
			 *
			 * @param string   $class_name CSS Class Name.
			 * @param string   $tag_name   HTML tag name.
			 * @param null|int $id         Attachment ID or null.
			 * @param mixed    $size       Size of image. Size name or array of width and
			 *                             height values. Null if not present.
			 */
			return (string) apply_filters( 'vll_lazy_loading_class', 'lazyload', $tag_name, $id, $size );
		}

		/**
		 * Sets aspect ratio attribute
		 *
		 * @param array $attrs A list of tag attributes.
		 *
		 * @return array A list of tag attributes.
		 */
		public static function set_aspectratio( $attrs ): array {
			if ( isset( self::$options['aspectratio'] ) && self::$options['aspectratio'] ) {
				$width  = isset( $attrs['width'] ) ? absint( $attrs['width'] ) : 0;
				$height = isset( $attrs['height'] ) ? absint( $attrs['height'] ) : 0;
				if ( $width && 0 !== $height ) {
					$attrs['data-aspectratio'] = "${width}/${height}";
				}
			}

			return $attrs;
		}

		/**
		 * Sets sizes attribute
		 *
		 * @param array $attrs A list of tag attributes.
		 * @return array A list of tag attributes.
		 */
		public static function set_sizes( $attrs ): array {
			if ( isset( self::$options['data-sizes'] ) && self::$options['data-sizes'] ) {
				$attrs['data-sizes'] = 'auto';
				unset( $attrs['sizes'] );
			}

			return $attrs;
		}

		/**
		 * Sets the native lazy loading attribute
		 *
		 * @param array $attrs A list of tag attributes.
		 * @return array A list of tag attributes.
		 */
		public static function set_native_loading( $attrs ): array {
			if ( isset( self::$options['native-loading'] ) && self::$options['native-loading'] ) {
				$attrs['loading'] = 'lazy';
			} else {
				unset( $attrs['loading'] );
			}

			return $attrs;
		}

		/**
		 * Search for attachment id in classes
		 *
		 * @param array $class_names A list of image classes.
		 * @return null|int Attachment ID if present or null.
		 */
		public static function search_id_in_classes( $class_names ): ?int {
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
		public static function search_size_in_classes( $class_names ): ?string {
			$size = null;
			foreach ( $class_names as $class_name ) {
				if ( 0 === strpos( $class_name, 'size-' ) ) {
					$size = str_replace( 'size-', '', $class_name );
				}
			}

			return $size;
		}

	}
endif;
