<?php
/**
 * Setup and output <input type="text">
 *
 * @package vralle-lazyload
 */

$output = sprintf(
	'<input name="%1$s[%2$s]" id="%1$s[%2$s]" class="%3$s" type="text" placeholder="%4$s" value="%5$s" maxlength="120" />',
	esc_attr( $settings_name ),
	esc_attr( $id ),
	isset( $args['class'] ) ? esc_attr( $args['class'] ) : '',
	isset( $args['placeholder'] ) ? esc_attr( $args['placeholder'] ) : '',
	isset( $settings[ $id ] ) ? esc_attr( $settings[ $id ] ) : ''
);

if ( isset( $args['label'] ) ) {
	$output = sprintf(
		'<label for="%1$s[%2$s]">%3$s %4$s</label>',
		esc_attr( $settings_name ),
		esc_attr( $id ),
		$output,
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

echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
