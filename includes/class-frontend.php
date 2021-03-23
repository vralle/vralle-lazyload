<?php
/**
 * The public-facing functionality of the plugin.
 *
 * PHP version 7.1
 *
 * @package    vralle-lazyload
 * @subpackage vralle-lazyload/includes
 * @author     V.Ralle <email4vit@gmail.com>
 * @copyright  Copyright (c) 2021, V.Ralle
 * @license    https://www.gnu.org/licenses/gpl-2.0.html
 * @since      1.1.0
 */

namespace VRalleLazyLoad;

\defined( 'ABSPATH' ) || exit;

use function \add_action;
use function \add_filter;
use function \array_merge;
use function \apply_filters;
use function \function_exists;
use function \get_query_var;
use function \intval;
use function \is_feed;
use function \is_preview;
use function \is_amp_endpoint;
use function \wp_enqueue_script;

if ( ! class_exists( __NAMESPACE__ . '\\Frontend' ) ) :

	/**
	 * The public-facing functionality of the plugin
	 */
	final class Frontend extends Lazyload {

		/**
		 * Init hooks
		 *
		 * @return void
		 */
		public static function init(): void {
			if ( self::is_exit() ) {
				return;
			}

			if ( isset( self::$options['avatars'] ) && self::$options['avatars'] ) {
				add_filter( 'get_avatar', array( __CLASS__, 'filter_avatar_html' ), PHP_INT_MAX );
			}

			if ( isset( self::$options['attachments'] ) && self::$options['attachments'] ) {
				add_filter( 'wp_get_attachment_image_attributes', array( __CLASS__, 'filter_attachment_attrs' ), PHP_INT_MAX, 3 );
				add_filter( 'get_image_tag', array( __CLASS__, 'filter_get_image_tag' ), PHP_INT_MAX, 6 );
				add_filter( 'post_thumbnail_html', array( __CLASS__, 'filter_post_thumbnail_html' ), PHP_INT_MAX, 4 );
				add_filter( 'render_block', array( __CLASS__, 'filter_block' ), PHP_INT_MAX, 2 );
			}

			add_filter( 'the_content', array( __CLASS__, 'filter_post_content' ), PHP_INT_MAX );

			if ( isset( self::$options['widgets'] ) && self::$options['widgets'] ) {
				add_filter( 'widget_text', array( __CLASS__, 'filter_widget' ), PHP_INT_MAX );
			}

			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ), 1 );
			add_filter( 'script_loader_tag', array( __CLASS__, 'filter_script_loader_tag' ), 10, 2 );
			add_filter( 'wp_head', array( __CLASS__, 'enqueue_styles' ) );

			if ( isset( self::$options['picturefill'] ) && self::$options['picturefill'] ) {
				add_action( 'wp_head', array( __CLASS__, 'load_picturefill' ) );
			}
		}

		/**
		 * Filters the HTML of the user's avatar.
		 *
		 * @param string $html HTML user avatar.
		 *
		 * @return string Content with filtered <img> tag.
		 */
		public static function filter_avatar_html( $html ): string {
			return self::do_content( $html, array( 'img' ) );
		}

		/**
		 * Filters the list of attachment image attributes
		 *
		 * @param array        $attrs      Attributes for the image markup.
		 * @param \WP_Post     $attachment Image attachment post.
		 * @param string|array $size       Requested size. Image size or array of width and height values.
		 *
		 * @return array A list of filtered attachment image attributes.
		 */
		public static function filter_attachment_attrs( $attrs, $attachment, $size ): array {
			return self::do_attrs( $attrs, 'img', $attachment->ID, $size );
		}

		/**
		 * Filters the attachment markup
		 *
		 * @param string       $html  HTML content for the image.
		 * @param int          $id    Attachment ID.
		 * @param string       $alt   Alternate text.
		 * @param string       $title Attachment title.
		 * @param string       $align Part of the class name for aligning the image.
		 * @param string|array $size  Size of image. Image size or array of width and height values (in that order).
		 *
		 * @return string      Content with filtered <img> tag.
		 */
		public static function filter_get_image_tag( $html, $id, $alt, $title, $align, $size ): string {
			return self::do_content( $html, array( 'img' ), $id, $size );
		}

		/**
		 * Filters the post thumbnail markup
		 *
		 * @param string       $html          The post thumbnail HTML.
		 * @param int          $post_id       The post ID.
		 * @param int          $attachment_id The post thumbnail ID.
		 * @param string|array $size          The post thumbnail size. Image size or array of width and height
		 *                                    values (in that order). Default 'post-thumbnail'.
		 *
		 * @return string Content with filtered <img> tag.
		 */
		public static function filter_post_thumbnail_html( $html, $post_id, $attachment_id, $size ): string {
			return self::do_content( $html, array( 'img' ), $attachment_id, $size );
		}

		/**
		 * Filters Gutenberg blocks.
		 *
		 * @param string $block_content The block content about to be appended.
		 * @param array  $block         The full block, including name and attributes.
		 */
		public static function filter_block( ?string $block_content, array $block ) : ?string {
			if ( ! isset( $block['blockName'] ) ) {
				return $block_content;
			}

			if ( 'core/image' === $block['blockName'] ) {
				$id   = isset( $block['attrs']['id'] ) ? (int) $block['attrs']['id'] : null;
				$size = isset( $block['attrs']['sizeSlug'] ) ? $block['attrs']['sizeSlug'] : null;

				return self::do_content( $block_content, array( 'img' ), $id, $size );
			}

			if ( 'core/image' === $block['blockName'] ) {
				$id   = null;
				$size = isset( $block['attrs']['sizeSlug'] ) ? $block['attrs']['sizeSlug'] : null;

				return self::do_content( $block_content, array( 'img' ), $id, $size );
			}

			return $block_content;
		}

		/**
		 * Filters the post content
		 *
		 * @param  string $html Content of the current post.
		 *
		 * @return string Content with filtered tags.
		 */
		public static function filter_post_content( $html ): string {
			$tag_names = array();

			if ( isset( self::$options['content_imgs'] ) && self::$options['content_imgs'] ) {
				$tag_names[] = 'img';
			}

			if ( isset( self::$options['embed'] ) && self::$options['embed'] ) {
				$tag_names = array_merge( $tag_names, self::get_embed_tag_names() );
			}

			if ( empty( $tag_names ) ) {
				return $html;
			}

			return self::do_content( $html, $tag_names );
		}

		/**
		 * Filters widgets content.
		 *
		 * @param string $html Widget Content.
		 *
		 * @return string Content with filtered tags.
		 */
		public static function filter_widget( $html ): string {
			$tag_names = array( 'img' );

			if ( isset( self::$options['embed'] ) && self::$options['embed'] ) {
				$tag_names = array_merge( $tag_names, self::get_embed_tag_names() );
			}

			if ( empty( $tag_names ) ) {
				return $html;
			}

			return self::do_content( $html, $tag_names );
		}

		/**
		 * Retrieves the names of the possible HTML tags to embed
		 *
		 * @return array A list of tag names.
		 */
		public static function get_embed_tag_names(): array {
			return array(
				'iframe',
			);
		}

		/**
		 * Registers scripts for the public-facing side of the site.
		 */
		public static function enqueue_scripts() {
			$dir_url = VLL_PLUGIN_URL . 'dist/lazysizes/';
			$plugins = self::get_plugins_list();

			// Load plugins before lazySizes.
			if ( ! empty( $plugins ) ) {
				foreach ( $plugins as $plugin ) {
					wp_enqueue_script(
						"lazysizes_ls_${plugin}",
						"${dir_url}plugins/${plugin}/ls.${plugin}.min.js",
						array(),
						'5.3.0',
						true
					);
				}
			}

			wp_enqueue_script(
				'lazysizes',
				"${dir_url}lazysizes-umd.min.js",
				array(),
				'5.3.0',
				true
			);
		}

		/**
		 * Retrieves a list of lazysize.js plugins
		 *
		 * @return array List of plugin names
		 */
		public static function get_plugins_list(): array {
			// List of plugins for possible settings.
			$plugins = array(
				'aspectratio',
				'native-loading',
				'object-fit',
				'parent-fit',
			);

			$load = array();
			foreach ( $plugins as $plugin ) {
				if ( isset( self::$options[ $plugin ] ) && self::$options[ $plugin ] ) {
					$load[] = $plugin;
				}
			}

			/**
			 * Filters the list of lazysizes.js plugins to load
			 *
			 * @param array $load List of plugin names.
			 */
			return (array) apply_filters( 'vll_lazysizes_plugins', $load );
		}

		/**
		 * Adds async attribute to lazysizes scripts.
		 *
		 * @param string $tag    The script tag.
		 * @param string $handle The script handle.
		 *
		 * @return string Script HTML tag.
		 */
		public static function filter_script_loader_tag( $tag, $handle ) {
			if ( 'lazysizes' == $handle ) {
				// Prevent adding attribute when already added in #12009.
				if ( ! preg_match( ':\sasync(=|>|\s):', $tag ) ) {
					$tag = preg_replace( ':(?=></script>):', ' async', $tag, 1 );
				}
			}

			return $tag;
		}

		/**
		 * Adds CSS for the public-facing side of the site.
		 */
		public static function enqueue_styles() {
			if ( isset( self::$options['placeholder-type'] ) && 'spinner' == self::$options['placeholder-type'] ) :
				?>
		<style media="screen">
		.lazyloading {
			background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0MCA0MCI+PHBhdGggb3BhY2l0eT0iLjIiIGQ9Ik0yMC4yMDEgNS4xNjljLTguMjU0IDAtMTQuOTQ2IDYuNjkyLTE0Ljk0NiAxNC45NDYgMCA4LjI1NSA2LjY5MiAxNC45NDYgMTQuOTQ2IDE0Ljk0NnMxNC45NDYtNi42OTEgMTQuOTQ2LTE0Ljk0NmMtLjAwMS04LjI1NC02LjY5Mi0xNC45NDYtMTQuOTQ2LTE0Ljk0NnptMCAyNi41OGMtNi40MjUgMC0xMS42MzQtNS4yMDgtMTEuNjM0LTExLjYzNCAwLTYuNDI1IDUuMjA5LTExLjYzNCAxMS42MzQtMTEuNjM0IDYuNDI1IDAgMTEuNjMzIDUuMjA5IDExLjYzMyAxMS42MzQgMCA2LjQyNi01LjIwOCAxMS42MzQtMTEuNjMzIDExLjYzNHoiLz48cGF0aCBkPSJNMjYuMDEzIDEwLjA0N2wxLjY1NC0yLjg2NmExNC44NTUgMTQuODU1IDAgMDAtNy40NjYtMi4wMTJ2My4zMTJjMi4xMTkgMCA0LjEuNTc2IDUuODEyIDEuNTY2eiI+PGFuaW1hdGVUcmFuc2Zvcm0gYXR0cmlidXRlVHlwZT0ieG1sIiBhdHRyaWJ1dGVOYW1lPSJ0cmFuc2Zvcm0iIHR5cGU9InJvdGF0ZSIgZnJvbT0iMCAyMCAyMCIgdG89IjM2MCAyMCAyMCIgZHVyPSIxcyIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiLz48L3BhdGg+PC9zdmc+);
			background-position: center center;
			background-size: 48px 48px;
			background-color: #fafafa;
			background-repeat: no-repeat;
		}
		</style>
				<?php
			endif;
		}

		/**
		 * Picterfill Loader
		 */
		public static function load_picturefill() {
			$src = VLL_PLUGIN_URL . 'dist/picturefill/picturefill.min.js';
			?>
			<script>(function(d,s,id) {
			if ('srcset' in d.createElement('img')) return;
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s);
			js.id = id;
			js.async = true;
			js.src = '<?php echo esc_url( $src ); ?>';
			fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'picturefill'));</script>
			<?php
		}

		/**
		 * State detection to skip processing
		 *
		 * @return boolean
		 */
		public static function is_exit() {
			/**
			 * Filters cancellation of all plugin handlers
			 *
			 * @param boolean
			 */
			if ( ! apply_filters( 'do_vralle_lazyload', true ) ) {
				return true;
			}

			if ( is_admin() ) {
				return true;
			}

			// Feed.
			if ( is_feed() ) {
				return true;
			}

			// On an AMP version of the posts.
			if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
				return true;
			}

			// Preview mode.
			if ( is_preview() ) {
				return true;
			}

			if ( is_customize_preview() ) {
				return true;
			}

			// Print.
			if ( 1 == intval( get_query_var( 'print' ) ) ) {
				return true;
			}

			// Print.
			if ( 1 == intval( get_query_var( 'printpage' ) ) ) {
				return true;
			}

			return false;
		}
	}

endif;
