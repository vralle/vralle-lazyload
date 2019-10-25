<?php
/**
 * Initial configuration of plugin settings
 *
 * @package vralle-lazyload
 */

namespace VRalleLazyLoad;

/**
 * Configuration of the plugin setting sections
 *
 * @return array
 */
function get_settings_sections() {
	$sections = array(
		array(
			'id'    => 'main',
			'title' => '',
		),
		array(
			'id'    => 'responsive',
			'title' => '',
		),
		array(
			'id'    => 'placeholder',
			'title' => '',
		),
		array(
			'id'    => 'ls-plugins',
			'title' => '',
		),
	);

	return $sections;
}

/**
 * Initial configuration of plugin settings
 *
 * @return array Initial configuration of settings
 */
function get_settings_config() {
	$main_config        = get_main_config();
	$responsive_config  = get_responsive_config();
	$placeholder_config = get_placeholder_config();
	$ls_plugins_config  = get_ls_plugins_config();
	$config             = array_merge( $main_config, $responsive_config, $placeholder_config, $ls_plugins_config );

	return apply_filters( 'vll_plugin_config', $config );
}

/**
 * Initial Configuration of General Section Settings
 *
 * @return array Initial Configuration of General Section Settings
 */
function get_main_config() {
	$config = array(
		array(
			'id'          => 'attachments',
			'type'        => 'checkbox',
			'default'     => '1',
			'title'       => __( 'Attachments', 'vralle-lazyload' ),
			'label'       => __( 'Lazy loading attachments.', 'vralle-lazyload' ),
			'description' => sprintf(
				/* translators: %s: Default option value */
				__( 'For example, the Post Thumbnails, Featured Images and Logo in Custom Header. Default "%s".', 'vralle-lazyload' ),
				__( 'Yes', 'vralle-lazyload' )
			),
			'section'     => 'main',
		),
		array(
			'id'          => 'content_imgs',
			'type'        => 'checkbox',
			'default'     => '1',
			'title'       => __( 'Post content', 'vralle-lazyload' ),
			'label'       => __( 'Lazy loading images in post content.', 'vralle-lazyload' ),
			'description' => sprintf(
				/* translators: %s: Default option value */
				__( 'Default "%s".', 'vralle-lazyload' ),
				__( 'Yes', 'vralle-lazyload' )
			),
			'section'     => 'main',
		),
		array(
			'id'          => 'widgets',
			'type'        => 'checkbox',
			'default'     => '1',
			'title'       => __( 'Widget content', 'vralle-lazyload' ),
			'label'       => __( 'Lazy loading of images in Text Widget and Custom HTML Widget.', 'vralle-lazyload' ),
			'description' => sprintf(
				/* translators: %s: Default option value */
				__( 'Default "%s".', 'vralle-lazyload' ),
				__( 'Yes', 'vralle-lazyload' )
			),
			'section'     => 'main',
		),
		array(
			'id'          => 'avatars',
			'type'        => 'checkbox',
			'default'     => '1',
			'title'       => __( 'Avatar', 'vralle-lazyload' ),
			'label'       => __( 'Lazy loading the Avatars.', 'vralle-lazyload' ),
			'description' => sprintf(
				/* translators: %s: Default option value */
				__( 'Default "%s".', 'vralle-lazyload' ),
				__( 'Yes', 'vralle-lazyload' )
			),
			'section'     => 'main',
		),
		array(
			'id'          => 'embed',
			'type'        => 'checkbox',
			'default'     => '1',
			'title'       => __( 'Embed', 'vralle-lazyload' ),
			'label'       => __( 'Lazy loading the embed, like iframe', 'vralle-lazyload' ),
			'description' => sprintf(
				/* translators: %s: Default option value */
				__( 'Default "%s".', 'vralle-lazyload' ),
				__( 'Yes', 'vralle-lazyload' )
			),
			'section'     => 'main',
		),
		array(
			'id'          => 'skip_class_names',
			'type'        => 'text',
			'placeholder' => 'class-1 class-2',
			'default'     => '',
			'title'       => \__( 'Exclude', 'vralle-lazyload' ),
			'label'       => \__( 'The name of CSS-class for images that need to be excluded from lazy loading.', 'vralle-lazyload' ),
			'description' => \__( 'Space separated', 'vralle-lazyload' ),
			'class'       => 'regular-text',
			'section'     => 'main',
		),
	);

	return $config;
}

/**
 * Initial Configuration of Responsive Section Settings
 *
 * @return array Initial Configuration of Responsive Section Settings
 */
function get_responsive_config() {
	$config = array(
		array(
			'id'          => 'picturefill',
			'type'        => 'checkbox',
			'default'     => '1',
			'title'       => __( 'Load picturefill.js', 'vralle-lazyload' ),
			'label'       => __( 'Support for responsive images in older browsers, like IE 10, 11.', 'vralle-lazyload' ),
			'description' => sprintf(
				/* translators: %s: Default option value */
				__( 'Default "%s".', 'vralle-lazyload' ),
				__( 'Yes', 'vralle-lazyload' )
			),
			'section'     => 'responsive',
		),
		array(
			'id'          => 'data-sizes',
			'type'        => 'checkbox',
			'default'     => '1',
			'title'       => 'Sizes',
			'label'       => __( 'Calculate sizes automatically.', 'vralle-lazyload' ),
			'description' => sprintf(
				/* translators: %s: Default option value */
				__( 'This will replace the data of sizes attribute. Default "%s".', 'vralle-lazyload' ),
				__( 'Yes', 'vralle-lazyload' )
			),
			'section'     => 'responsive',
		),
	);

	return $config;
}

/**
 * Initial Configuration of Placeholder Section Settings
 *
 * @return array Initial Configuration of Placeholder Section Settings
 */
function get_placeholder_config() {
	$config = array(
		array(
			'id'        => 'placeholder-type',
			'type'      => 'select',
			'default'   => 'transparent',
			'title'     => __( 'Placeholder type', 'vralle-lazyload' ),
			'options'   => array(
				'transparent' => 'Transparent',
				'spinner'     => 'Spinner',
			),
			'label_for' => 'vralle-lazyload[placeholder-type]',
			'section'   => 'placeholder',
		),
	);

	return $config;
}

/**
 * Initial Configuration of LQIP Section Settings
 *
 * @return array Initial Configuration of LazyLoad Plugins Section Settings
 */
function get_ls_plugins_config() {
	$config = array(
		array(
			'id'          => 'aspectratio',
			'type'        => 'checkbox',
			'default'     => null,
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
			'section'     => 'ls-plugins',
		),
		array(
			'id'          => 'native-loading',
			'type'        => 'checkbox',
			'default'     => null,
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
			'section'     => 'ls-plugins',
		),
	);

	return $config;
}
