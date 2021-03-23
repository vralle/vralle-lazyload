<?php
/**
 * Initial configuration of plugin settings
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

use function \__;
use function \apply_filters;
use function \array_map;
use function \explode;
use function \implode;
use function \register_setting;
use function \sanitize_text_field;
use function \sprintf;
use function \str_replace;

if ( ! class_exists( __NAMESPACE__ . '\\BaseSettings' ) ) :
	/**
	 * The plugin configuration
	 */
	class BaseSettings {

		/**
		 * The plugin option name
		 *
		 * @var string
		 */
		protected static string $option_name = 'vralle-lazyload';

		/**
		 * The plugin option group name;
		 *
		 * @var string
		 */
		protected static string $option_group = 'vralle-lazyload';

		/**
		 * The plugin settings page slug
		 *
		 * @var string
		 */
		protected static string $page_slug = 'vralle-lazyload';

		/**
		 * The plugin options
		 *
		 * @var array
		 */
		protected static $options;

		/**
		 * Init Class
		 *
		 * @return void
		 */
		public static function init(): void {
			self::register_settings();
			self::$options = get_option( self::$option_name );
		}

		/**
		 * Registers the plugin settings
		 *
		 * @return void
		 */
		public static function register_settings(): void {
			register_setting(
				self::$option_group,
				self::$option_name,
				array(
					'sanitize_callback' => array( __CLASS__, 'sanitize_option' ),
					'default'           => self::get_settings_default(),
				)
			);
		}

		/**
		 * Sanitizes the option value
		 *
		 * @param array $option The plugin settings.
		 *
		 * @return array Sanitized option value.
		 */
		public static function sanitize_option( $option ): array {
			$config = self::get_settings_properties();
			$out    = array();

			foreach ( $option as $key => $value ) {
				if ( 'skip_class_names' == $key ) {
					$value    = sanitize_text_field( $value );
					$value    = str_replace( '.', ' ', $value );
					$to_array = explode( ' ', $value );
					$to_array = array_map( 'sanitize_html_class', $to_array );

					$out[ $key ] = implode( ' ', $to_array );

				} elseif ( isset( $config[ $key ] ) ) {

					switch ( $config[ $key ]['type'] ) {
						case 'bool':
							$out[ $key ] = (bool) $value;
							break;
						default:
							$out[ $key ] = sanitize_text_field( $value );
							break;
					}
				}
			}

			return $out;
		}

		/**
		 * Retrieves configuration of the plugin settings
		 *
		 * @return array Configuration of the plugin settings.
		 */
		public static function get_settings_config(): array {
			$config = array(
				self::get_main_section_config(),
				self::get_responsive_section_config(),
				self::get_placeholder_section_config(),
				self::get_ls_plugins_section_config(),
			);

			/**
			 * Filters the plugin settings configuration
			 *
			 * @param array $config The plugin settings configuration.
			 */
			return (array) apply_filters( 'vll_plugin_config', $config );
		}

		/**
		 * Retrieves the settings and their default values
		 *
		 * @return array List of settings and their default values.
		 */
		public static function get_settings_default(): array {
			$option_config = array();
			$plugin_config = self::get_settings_config();
			foreach ( $plugin_config as $section ) {
				foreach ( $section['fields'] as $field ) {
					$option_config[ $field['id'] ] = $field['default'];
				}
			}

			return $option_config;
		}

		/**
		 * Retrieves the settings and their properties
		 *
		 * @return array The settings and their properties
		 */
		public static function get_settings_properties(): array {
			$option_config = array();
			$plugin_config = self::get_settings_config();
			foreach ( $plugin_config as $section ) {
				foreach ( $section['fields'] as $field ) {
					$option_config[ $field['id'] ] = array(
						'default' => $field['default'],
						'type'    => $field['type'],
					);
				}
			}

			return $option_config;
		}

		/**
		 * Initial configuration of the main section
		 *
		 * @return array Configuration of the main section.
		 */
		public static function get_main_section_config(): array {
			$config = array(
				'id'     => 'main',
				'title'  => __( 'Main settings', 'vralle-lazyload' ),
				'fields' => array(
					array(
						'id'          => 'attachments',
						'type'        => 'bool',
						'default'     => 1,
						'title'       => __( 'Attachments', 'vralle-lazyload' ),
						'label'       => __( 'Lazy loading attachments.', 'vralle-lazyload' ),
						'description' => sprintf(
							/* translators: %s: Default option value */
							__( 'For example, the Post Thumbnails, Featured Images and Logo in Custom Header. Default "%s".', 'vralle-lazyload' ),
							__( 'Yes', 'vralle-lazyload' )
						),
						'input'       => array(
							'type' => 'checkbox',
						),
					),
					array(
						'id'          => 'content_imgs',
						'type'        => 'bool',
						'default'     => 1,
						'title'       => __( 'Post content', 'vralle-lazyload' ),
						'label'       => __( 'Lazy loading images in post content.', 'vralle-lazyload' ),
						'description' => sprintf(
							/* translators: %s: Default option value */
							__( 'Default "%s".', 'vralle-lazyload' ),
							__( 'Yes', 'vralle-lazyload' )
						),
						'input'       => array(
							'type' => 'checkbox',
						),
					),
					array(
						'id'          => 'widgets',
						'type'        => 'bool',
						'default'     => 1,
						'title'       => __( 'Widget content', 'vralle-lazyload' ),
						'label'       => __( 'Lazy loading of images in Text Widget and Custom HTML Widget.', 'vralle-lazyload' ),
						'description' => sprintf(
							/* translators: %s: Default option value */
							__( 'Default "%s".', 'vralle-lazyload' ),
							__( 'Yes', 'vralle-lazyload' )
						),
						'input'       => array(
							'type' => 'checkbox',
						),
					),
					array(
						'id'          => 'avatars',
						'type'        => 'bool',
						'default'     => 1,
						'title'       => __( 'Avatar', 'vralle-lazyload' ),
						'label'       => __( 'Lazy loading the Avatars.', 'vralle-lazyload' ),
						'description' => sprintf(
							/* translators: %s: Default option value */
							__( 'Default "%s".', 'vralle-lazyload' ),
							__( 'Yes', 'vralle-lazyload' )
						),
						'input'       => array(
							'type' => 'checkbox',
						),
					),
					array(
						'id'          => 'embed',
						'type'        => 'bool',
						'default'     => 1,
						'title'       => __( 'Embed', 'vralle-lazyload' ),
						'label'       => __( 'Lazy loading the embed, like iframe', 'vralle-lazyload' ),
						'description' => sprintf(
							/* translators: %s: Default option value */
							__( 'Default "%s".', 'vralle-lazyload' ),
							__( 'Yes', 'vralle-lazyload' )
						),
						'input'       => array(
							'type' => 'checkbox',
						),
					),
					array(
						'id'          => 'skip_class_names',
						'type'        => 'string',
						'default'     => '',
						'title'       => __( 'Exclude by class names', 'vralle-lazyload' ),
						'label'       => __( 'The name of CSS-class for images that need to be excluded from lazy loading.', 'vralle-lazyload' ),
						'description' => __( 'Space separated', 'vralle-lazyload' ),
						'input'       => array(
							'type'        => 'text',
							'placeholder' => 'class-1 class-2',
						),
					),
				),
			);

			return $config;
		}

		/**
		 * Initial configuration of the responsive images section
		 *
		 * @return array Configuration of the responsive images section.
		 */
		public static function get_responsive_section_config(): array {
			$config = array(
				'id'     => 'responsive',
				'title'  => __( 'Responsive images', 'vralle-lazyload' ),
				'fields' => array(
					array(
						'id'          => 'picturefill',
						'type'        => 'bool',
						'default'     => 0,
						'title'       => __( 'Load picturefill.js', 'vralle-lazyload' ),
						'label'       => __( 'Support for responsive images in older browsers, like IE 10, 11.', 'vralle-lazyload' ),
						'description' => sprintf(
							/* translators: %s: Default option value */
							__( 'Default "%s".', 'vralle-lazyload' ),
							__( 'Yes', 'vralle-lazyload' )
						),
						'input'       => array(
							'type' => 'checkbox',
						),
					),
					array(
						'id'          => 'data-sizes',
						'type'        => 'bool',
						'default'     => 1,
						'title'       => 'Sizes',
						'label'       => __( 'Calculate sizes automatically.', 'vralle-lazyload' ),
						'description' => sprintf(
							/* translators: %s: Default option value */
							__( 'This will replace the data of sizes attribute. Default "%s".', 'vralle-lazyload' ),
							__( 'Yes', 'vralle-lazyload' )
						),
						'input'       => array(
							'type' => 'checkbox',
						),
					),
				),
			);

			return $config;
		}

		/**
		 * Initial Configuration of Placeholder Section
		 *
		 * @return array Configuration of Placeholder Section.
		 */
		public static function get_placeholder_section_config(): array {
			$config = array(
				'id'     => 'placeholder',
				'title'  => __( 'Image placeholder', 'vralle-lazyload' ),
				'fields' => array(
					array(
						'id'        => 'placeholder-type',
						'type'      => 'string',
						'default'   => 'transparent',
						'title'     => __( 'Placeholder type', 'vralle-lazyload' ),
						'label_for' => self::$option_name . '[placeholder-type]',
						'input'     => array(
							'type'    => 'select',
							'options' => array(
								'transparent' => __( 'Transparent', 'vralle-lazyload' ),
								'spinner'     => __( 'Spinner', 'vralle-lazyload' ),
							),
						),
					),
				),
			);

			return $config;
		}

		/**
		 * Initial Configuration of lazysizes.js plugins Section
		 *
		 * @return array Configuration of Lazysizes.js plugins section.
		 */
		public static function get_ls_plugins_section_config(): array {
			$config = array(
				'id'     => 'ls-plugins',
				'title'  => __( 'Lazysizes.js plugins', 'vralle-lazyload' ),
				'fields' => array(
					array(
						'id'          => 'aspectratio',
						'type'        => 'bool',
						'default'     => 0,
						'title'       => 'aspectratio',
						'label'       => sprintf(
							/* translators: %s: Extension name */
							__( 'Load %s extension.', 'vralle-lazyload' ),
							'"aspectratio"'
						),
						'description' => sprintf(
							/* translators: %s: Default option value */
							__( 'This plugin helps to pre-occupy the space needed for an image by calculating the height from the image width or the width from the height (This means the width or height has to be calculable before the image is loaded). Default "%s".', 'vralle-lazyload' ),
							__( 'No', 'vralle-lazyload' )
						),
						'input'       => array(
							'type' => 'checkbox',
						),
					),
					array(
						'id'          => 'native-loading',
						'type'        => 'bool',
						'default'     => 0,
						'title'       => 'native-loading',
						'label'       => sprintf(
							/* translators: %s: Extension name */
							__( 'Load %s extension.', 'vralle-lazyload' ),
							'"native-loading"'
						),
						'description' => sprintf(
							/* translators: %s: Default option value */
							__( 'This extension automatically transforms img.lazyload/iframe.lazyload elements in browsers that support native lazy loading. Default "%s".', 'vralle-lazyload' ),
							__( 'No', 'vralle-lazyload' )
						),
						'input'       => array(
							'type' => 'checkbox',
						),
					),
				),
			);

			return $config;
		}
	}

endif;
