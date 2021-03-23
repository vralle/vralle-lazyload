<?php
/**
 * The plugin settings page
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

use \WP_Screen;
use function \add_action;
use function \add_options_page;
use function \add_settings_field;
use function \add_settings_section;
use function \apply_filters;
use function \checked;
use function \class_exists;
use function \do_settings_sections;
use function \esc_attr;
use function \get_admin_page_title;
use function \settings_fields;
use function \sprintf;
use function \submit_button;
use function \wp_kses_post;

if ( ! class_exists( __NAMESPACE__ . '\\Admin_UI' ) ) :
	/**
	 * The plugin settings page
	 */
	final class Admin_UI extends BaseSettings {

		/**
		 * The plugin settings page screen identifier
		 *
		 * @var string
		 */
		protected static $hook_suffix;

		/**
		 * Init admin side hooks
		 *
		 * @return void
		 */
		public static function init(): void {
			add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );
			add_action( 'current_screen', array( __CLASS__, 'add_settings_fields' ) );
			add_action( 'current_screen', array( __CLASS__, 'load_textdomain' ), 1 );
			add_filter( 'plugin_action_links_' . VLL_PLUGIN_BASENAME, array( __CLASS__, 'add_settings_link' ) );
		}

		/**
		 * Adds a settings page to the admin menu
		 *
		 * @return void
		 */
		public static function add_settings_page(): void {
			$page_title = 'VRALLE.Lazyload';
			$menu_title = 'Lazyload';
			/**
			 * Filters capabilities of the plugin settings page.
			 */
			$capability = apply_filters( 'vll_settings_capability', 'manage_options' );

			static::$hook_suffix = add_options_page(
				$page_title,
				$menu_title,
				$capability,
				self::$page_slug,
				array( __CLASS__, 'settings_page_markup' ),
			);
		}

		/**
		 * Displays the content of the plugin settings page
		 *
		 * @return void
		 */
		public static function settings_page_markup(): void {
			?>
			<div class="wrap">
				<h1><?php echo wp_kses_post( get_admin_page_title() ); ?></h1>

				<?php settings_errors( self::$option_name ); ?>

				<form method="post" action="options.php">
					<?php
						settings_fields( self::$option_group );
						do_settings_sections( self::$page_slug );
						submit_button();
					?>
				</form>
			</div>
			<?php
		}

		/**
		 * Adds settings fields.
		 *
		 * @param WP_Screen $current_screen Current WP_Screen object.
		 *
		 * @return void
		 */
		public static function add_settings_fields( WP_Screen $current_screen ): void {
			if ( self::$hook_suffix != $current_screen->base ) {
				return;
			}
			$settings_config = self::get_settings_config();
			$option_name     = self::$option_name;
			$page_slug       = self::$page_slug;

			foreach ( $settings_config as $section ) {

				add_settings_section(
					$section['id'],
					$section['title'],
					null,
					$page_slug
				);

				foreach ( $section['fields'] as $field ) {
					$field_id = $field['id'];
					add_settings_field(
						"${option_name}[${field_id}]",
						$field['title'],
						array( __CLASS__, 'get_field' ),
						$page_slug,
						$section['id'],
						$field
					);
				}
			}
		}

		/**
		 * Defines the method for building the field
		 *
		 * @param array $args The field arguments.
		 *
		 * @return callable Field method.
		 */
		public static function get_field( $args ): ?callable {
			$field_type = $args['input']['type'];
			$callback   = "field_${field_type}";

			if ( method_exists( __CLASS__, $callback ) ) {
				return self::$callback( $args );
			}
		}

		/**
		 * Builds markup of the checkbox field
		 *
		 * @param array $args The field arguments.
		 *
		 * @return void
		 */
		public static function field_checkbox( $args ): void {
			$value = self::get_option( $args['id'], 0 );
			$name  = self::$option_name;
			$id    = $args['id'];

			$output = '<fieldset>';
			if ( ! empty( $args['title'] ) ) {
				$output .= '<legend class="screen-reader-text"><span>' . $args['title'] . '</span></legend>';
			}
			$input = sprintf(
				'<input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s />',
				esc_attr( "${name}[${id}]" ),
				checked( $value, 1, false )
			);

			$output .= sprintf(
				'<label for="%1$s">%2$s %3$s</label>',
				esc_attr( "${name}[${id}]" ),
				$input,
				esc_attr( $args['label'] )
			);

			$output .= static::field_additional( $args );

			echo $output;
		}

		/**
		 * Builds markup of text field
		 *
		 * @param array $args The field arguments.
		 *
		 * @return void
		 */
		public static function field_text( $args ): void {
			$value = static::get_option( $args['id'] );
			$name  = self::$option_name;
			$id    = $args['id'];

			$output = sprintf(
				'<input type="text" id="%1$s" name="%1$s" value="%2$s" class="%3$s" />',
				esc_attr( "${name}[${id}]" ),
				esc_attr( $value ),
				isset( $args['input']['class'] ) ? $args['input']['class'] : 'regular-text'
			);

			$output .= static::field_additional( $args );

			echo $output;
		}

		/**
		 * Builds markup of select field
		 *
		 * @param array $args The field arguments.
		 *
		 * @return void
		 */
		public static function field_select( $args ): void {
			$value = static::get_option( $args['id'] );
			$name  = self::$option_name;
			$id    = $args['id'];

			$output = sprintf(
				'<select id="%1$s" name="%1$s">',
				"${name}[${id}]"
			);
			foreach ( $args['input']['options'] as $key => $text ) {
				$output .= sprintf(
					'<option value="%1$s" %2$s>%3$s</option>',
					esc_attr( $key ),
					selected( $key, $value, false ),
					esc_attr( $text )
				);
			}
			$output .= '</select>';

			echo $output;
		}

		/**
		 * Builds the field description markup
		 *
		 * @param array $args The field arguments.
		 *
		 * @return string The field description markup.
		 */
		private static function field_additional( $args ): ?string {
			$additional = '';
			if ( ! empty( $args['description'] ) ) {
				$additional = sprintf(
					'<p class="description">%s</p>',
					wp_kses_post( $args['description'] )
				);
			}

			return $additional;
		}

		/**
		 * Retrieves the settings option
		 *
		 * @param string $name The settings option name.
		 * @param mixed  $fallback Fallback value.
		 * @return mixed The settings option value.
		 */
		public static function get_option( $name, $fallback = '' ) {
			if ( self::$options && isset( self::$options[ $name ] ) ) {
				$value = self::$options[ $name ];
			} else {
				$value = $fallback;
			}

			return $value;
		}

		/**
		 * Loads the plugin text domain.
		 *
		 * @param WP_Screen $current_screen Current WP_Screen object.
		 *
		 * @return void
		 */
		public static function load_textdomain( WP_Screen $current_screen ): void {
			if ( self::$hook_suffix != $current_screen->base ) {
				return;
			}
			load_plugin_textdomain(
				'vralle-lazyload',
				false,
				dirname( VLL_PLUGIN_BASENAME ) . '/languages/'
			);
		}

		/**
		 * Adds link to plugin setting page on plugins page.
		 *
		 * @param array $actions      An array of plugin action links.
		 *                            By default this can include 'activate', 'deactivate', and 'delete'.
		 *                            With Multisite active this can also include
		 *                            'network_active' and 'network_only' items.
		 *
		 * @return array Plugin links
		 */
		public static function add_settings_link( array $actions ): array {
			$settings = array(
				sprintf(
					'<a href="%1$s">%2$s</a>',
					menu_page_url( self::$page_slug, false ),
					esc_html__( 'Settings', 'vralle-lazyload' )
				),
			);

			return array_merge( $settings, $actions );
		}

	}

endif;
