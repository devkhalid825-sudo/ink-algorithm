<?php
/**
 * Front-end dependency probe (administrators only, read-only).
 *
 * Add  ?nkc_probe=1  to any front-end URL while logged in as an administrator. The page then ends with an HTML comment
 * listing scripts/styles that were enqueued but NEVER printed because a dependency (typically a handle that the
 * original theme used to register) does not exist. WordPress skips such assets silently, which shows up as
 * "widget renders but slider/tabs/animation does nothing".
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_footer', function () {
	if ( empty( $_GET['nkc_probe'] ) || ! current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	$out = array();
	foreach ( array( 'script' => wp_scripts(), 'style' => wp_styles() ) as $kind => $deps ) {
		foreach ( (array) $deps->queue as $handle ) {
			if ( in_array( $handle, (array) $deps->done, true ) ) {
				continue;
			}
			$missing = array();
			$seen    = array();
			$walk    = function ( $h ) use ( &$walk, &$missing, &$seen, $deps ) {
				if ( isset( $seen[ $h ] ) ) {
					return;
				}
				$seen[ $h ] = true;
				if ( ! isset( $deps->registered[ $h ] ) ) {
					$missing[] = $h;
					return;
				}
				foreach ( (array) $deps->registered[ $h ]->deps as $d ) {
					$walk( $d );
				}
			};
			$walk( $handle );
			$out[] = sprintf( '%s "%s" NOT PRINTED%s', $kind, $handle, $missing ? ' — missing dependency: ' . implode( ', ', $missing ) : ' (printed later / conditional)' );
		}
	}
	echo "\n<!-- NK Neuros Compat probe (" . esc_html( NK_NEUROS_COMPAT_VERSION ) . ") theme=" . esc_html( get_stylesheet() ) . "\n" . str_replace( '>', '&gt;', $out ? implode( "\n", $out ) : 'all enqueued scripts/styles were printed' ) . "\n-->\n";
}, PHP_INT_MAX );
