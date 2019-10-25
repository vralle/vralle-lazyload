<?php
/**
 * Setup and output <select>
 *
 * @package vralle-lazyload
 */

if ( ! isset( $settings[ $id ] ) ) {
	$defaults        = VRalleLazyLoad\get_default_settings();
	$settings[ $id ] = $defaults[ $id ];
}
$output = sprintf(
	'<select name="%1$s[%2$s]" id="%1$s[%2$s]">',
	esc_attr( $settings_name ),
	esc_attr( $id ),
);
foreach ( $args['options'] as $key => $text ) {
	$output .= sprintf(
		'<option value="%s"%s>%s</option>',
		esc_attr( $key ),
		selected( $settings[ $id ], $key, false ),
		esc_html( $text )
	);
}
$output .= '</select>';

if ( isset( $args['label'] ) ) {
	$output .= sprintf(
		' <label for="%1$s[%2$s]">%3$s</label>',
		esc_attr( $settings_name ),
		esc_attr( $id ),
		esc_html( $args['label'] )
	);
}

if ( isset( $args['description'] ) ) {
	$allowed_html = array(
		'a' => array(
			'href' => true,
		),
	);
	$output      .= sprintf(
		'<p class="description">%s</p>',
		wp_kses( $args['description'], $allowed_html, array( 'http', 'https' ) )
	);
}

echo $output;  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
