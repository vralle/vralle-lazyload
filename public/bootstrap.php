<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package vralle-lazyload
 */

namespace VRalleLazyLoad;

use function add_filter;
use function array_merge;
use function apply_filters;
use function dirname;
use function function_exists;
use function get_query_var;
use function intval;
use function is_feed;
use function is_preview;
use function is_amp_endpoint;
use function plugins_url;
use function wp_register_script;
use function wp_script_add_data;
use function wp_enqueue_script;

/**
 * Set up hooked callbacks on plugins_loaded
 */
function bootstrap() {
	add_filter( 'get_avatar', __NAMESPACE__ . '\\avatar_html', PHP_INT_MAX, 3 );
	add_filter( 'get_image_tag', __NAMESPACE__ . '\\filter_get_image_tag', PHP_INT_MAX, 6 );
	add_filter( 'post_thumbnail_html', __NAMESPACE__ . '\\filter_post_thumbnail_html', PHP_INT_MAX, 4 );
	add_filter( 'the_content', __NAMESPACE__ . '\\post_content', PHP_INT_MAX );
	add_filter( 'widget_text', __NAMESPACE__ . '\\widget_content', PHP_INT_MAX );
	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_scripts', 1 );
	add_filter( 'wp_get_attachment_image_attributes', __NAMESPACE__ . '\\attachment_attrs', PHP_INT_MAX, 3 );
	add_action( 'wp_head', __NAMESPACE__ . '\\add_picturefill' );
	add_filter( 'wp_kses_allowed_html', __NAMESPACE__ . '\\allow_lazyload_html' );
	add_filter( 'wp_head', __NAMESPACE__ . '\\enqueue_styles' );
}

/**
 * Retrieve the list of attachment image attributes
 *
 * @param array        $attrs      Attributes for the image markup.
 * @param \WP_Post     $attachment Image attachment post.
 * @param string|array $size       Requested size. Image size or array of width and height values.
 * @return array A list of filtered attachment image attributes.
 */
function attachment_attrs( $attrs, $attachment, $size ) {
	if ( ! get_option( 'attachments' ) || is_exit() ) {
		return $attrs;
	}

	return attr_handler( $attrs, 'img', $attachment->ID, $size );
}

/**
 * Retrieve the avatar markup
 *
 * @param string $html    output of the avatar.
 * @param mixed  $user_id The Gravatar to retrieve. Accepts a user_id, gravatar md5 hash,
 *                        user email, WP_User object, WP_Post object, or WP_Comment object.
 * @param int    $size    Square avatar width and height in pixels to retrieve.
 * @return string Content with filtered <img> tag.
 */
function avatar_html( $html, $user_id, $size ) {
	if ( ! get_option( 'avatars' ) || is_exit() ) {
		return $html;
	}

	$id = null;

	return content_handler( $html, array( 'img' ), $id, array( $size, $size ) );
}

/**
 * Retrive the attachment markup
 *
 * @param string       $html  HTML content for the image.
 * @param int          $id    Attachment ID.
 * @param string       $alt   Alternate text.
 * @param string       $title Attachment title.
 * @param string       $align Part of the class name for aligning the image.
 * @param string|array $size  Size of image. Image size or array of width and height values (in that order).
 * @return string      Content with filtered <img> tag.
 */
function filter_get_image_tag( $html, $id, $alt, $title, $align, $size ) {
	if ( ! get_option( 'attachments' ) || is_exit() ) {
		return $html;
	}
	return content_handler( $html, array( 'img' ), $id, $size );
}

/**
 * Retrieve the post thumbnail markup
 *
 * @param string       $html          The post thumbnail HTML.
 * @param int          $post_id       The post ID.
 * @param int          $attachment_id The post thumbnail ID.
 * @param string|array $size          The post thumbnail size. Image size or array of width and height
 *                                    values (in that order). Default 'post-thumbnail'.
 * @return string Content with filtered <img> tag.
 */
function filter_post_thumbnail_html( $html, $post_id, $attachment_id, $size ) {
	if ( ! get_option( 'attachments' ) || is_exit() ) {
		return $html;
	}
	return content_handler( $html, array( 'img' ), $attachment_id, $size );
}

/**
 * Retrieve the post content
 *
 * @param  string $html Content of the current post.
 * @return string Content with filtered tags.
 */
function post_content( $html ) {
	if ( is_exit() ) {
		return $html;
	}

	$tag_names = array();

	if ( get_option( 'content_imgs' ) ) {
		$tag_names[] = 'img';
	}

	if ( get_option( 'embed' ) ) {
		$tag_names = array_merge( $tag_names, get_embed_tag_names() );
	}

	if ( empty( $tag_names ) ) {
		return $html;
	}

	$id   = null;
	$size = null;

	return content_handler( $html, $tag_names, $id, $size );
}

/**
 * Retrieve the content of Widgets.
 *
 * @param string $html Widget Content.
 * @return string Content with filtered tags.
 */
function widget_content( $html ) {
	if ( is_exit() ) {
		return $html;
	}

	$tag_names = array();

	if ( get_option( 'widgets' ) ) {
		$tag_names[] = 'img';
	}

	if ( get_option( 'embed' ) ) {
		$tag_names = array_merge( $tag_names, get_embed_tag_names() );
	}

	if ( empty( $tag_names ) ) {
		return $html;
	}

	$id   = null;
	$size = null;

	return content_handler( $html, $tag_names, $id, $size );
}

/**
 * Retrieve the names of the possible HTML tags to embed
 *
 * @return array A list of tag names.
 */
function get_embed_tag_names() {
	return array(
		'iframe',
	);
}

/**
 * Allow lazyload attributes in HTML tags
 *
 * @param array $allowed_tags The allowed tags and their attributes.
 * @return array Filtered allowed tags.
 */
function allow_lazyload_html( $allowed_tags ) {
	$allowed_attrs = array(
		'class'            => 1,
		'data-aspectratio' => 1,
		'data-src'         => 1,
		'data-srcset'      => 1,
		'data-sizes'       => 1,
		'loading'          => 1,
	);

	$tag_names = array_merge( array( 'img' ), get_embed_tag_names() );

	foreach ( $tag_names as $name ) {
		if ( isset( $allowed_tags[ $name ] ) ) {
			$allowed_tags[ $name ] = array_merge(
				$allowed_tags[ $name ],
				$allowed_attrs
			);
		}
	}
	return $allowed_tags;
}

/**
 * Register the JavaScript for the public-facing side of the site.
 */
function enqueue_scripts() {
	if ( is_exit() ) {
		return;
	}

	$debug_suffix      = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	$lazysized_dir_url = VLL_PLUGIN_URL . 'dist/lazysizes/';
	$lazysizes_id      = get_settings_name();

	wp_register_script(
		$lazysizes_id,
		$lazysized_dir_url . 'lazysizes-umd' . $debug_suffix . '.js',
		array(),
		'5.1.2',
		true
	);

	$plugins = get_plugins_list();

	// Include the plugin scripts before the lazySizes main script.
	if ( ! empty( $plugins ) ) {
		foreach ( $plugins as $plugin ) {
			wp_enqueue_script(
				get_settings_name() . '_ls.' . $plugin,
				$lazysized_dir_url . 'plugins/' . $plugin . '/ls.' . $plugin . $debug_suffix . '.js',
				array(),
				'5.1.2',
				true
			);
		}
	}

	wp_enqueue_script( $lazysizes_id );
}

/**
 * Add styles for the public-facing side of the site.
 */
function enqueue_styles() {
	if ( is_exit() ) {
		return;
	}

	if ( 'spinner' === get_option( 'placeholder-type' ) ) {
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
	}
}

/**
 * Picterfill Loader
 */
function add_picturefill() {
	if ( ! get_option( 'picturefill' ) || is_exit() ) {
		return;
	}

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
 * Create a list of lazysize.js plugins for enqueue
 *
 * @return  array A list of plugins
 */
function get_plugins_list() {
	// Extensions list from options for possible load.
	$list_of_extensions = array(
		'aspectratio',
		'native-loading',
		'object-fit',
		'parent-fit',
	);

	$plugins = array();
	foreach ( $list_of_extensions as $extension ) {
		if ( get_option( $extension ) ) {
			$plugins[] = $extension;
		}
	}

	/**
	 * Filter a list of lazysizes plugins
	 *
	 * @param array $plugins a list of lazysizes plugins
	 */
	return apply_filters( 'vll_lazysizes_plugins', $plugins );
}
