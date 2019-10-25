<?php
/**
 * Setup and output <input type="checkbox">
 *
 * @package vralle-lazyload
 */

$output = sprintf(
	'<input type="checkbox" id="%1$s[%2$s]" name="%1$s[%2$s]" value="1" %3$s>',
	esc_attr( $settings_name ),
	esc_attr( $id ),
	checked( isset( $settings[ $id ] ), true, false )
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
