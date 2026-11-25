<?php
/**
 * Static dependency scanner for the ORIGINAL plugins.
 *
 * Tokenizes every PHP file of neuros-plugin / lastudio-element-kit (nothing is executed) and reports:
 *   1. unresolved functions  — called but not defined by WordPress, PHP, any scanned file, or an active plugin/theme.
 *                              These are the "undefined function" fatals, found BEFORE a visitor hits them.
 *   2. theme-path usage      — get_template_directory*(), get_template_part(), locate_template(), ...
 *   3. theme feature checks  — current_theme_supports( 'x' ), get_theme_mod( 'x' ), get_option( 'neuros...' )
 *   4. hooks the plugin LISTENS to but never fires itself (the original theme fired them)
 *   5. script/style dependencies on handles the original theme registered
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nkc_scan_plugins() {
	return apply_filters( 'nkc_scan_plugins', array( 'neuros-plugin', 'lastudio-element-kit' ) );
}

function nkc_php_files( $dir ) {
	$files = array();
	if ( ! is_dir( $dir ) ) {
		return $files;
	}
	$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
	foreach ( $it as $f ) {
		if ( $f->isFile() && 'php' === strtolower( $f->getExtension() ) && false === strpos( wp_normalize_path( $f->getPathname() ), '/vendor/' ) ) {
			$files[] = $f->getPathname();
		}
	}
	return $files;
}

/** Significant (non-whitespace/comment) tokens. */
function nkc_tokens( $code ) {
	$out  = array();
	$skip = array( T_WHITESPACE, T_COMMENT, T_DOC_COMMENT );
	foreach ( token_get_all( $code ) as $t ) {
		if ( is_array( $t ) ) {
			if ( in_array( $t[0], $skip, true ) ) {
				continue;
			}
			$out[] = $t;
		} else {
			$out[] = array( 0, $t, 0 );
		}
	}
	return $out;
}

function nkc_scan() {
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 120 ); // phpcs:ignore
	}
	$root    = wp_normalize_path( WP_PLUGIN_DIR );
	$defined = array(); // lowercase fq function name => true
	$calls   = array(); // list of [fq candidates, name, file, line]
	$info    = array( 'theme_paths' => array(), 'theme_checks' => array(), 'listens' => array(), 'fires' => array(), 'deps' => array() );
	$scanned = array();
	$name_t  = array( T_STRING );
	foreach ( array( 'T_NAME_QUALIFIED', 'T_NAME_FULLY_QUALIFIED' ) as $c ) {
		if ( defined( $c ) ) {
			$name_t[] = constant( $c );
		}
	}
	$path_fns  = array( 'get_template_directory', 'get_template_directory_uri', 'get_stylesheet_directory', 'get_stylesheet_directory_uri', 'get_template_part', 'locate_template', 'get_theme_file_path', 'get_theme_file_uri', 'get_parent_theme_file_path', 'get_parent_theme_file_uri' );
	$check_fns = array( 'current_theme_supports', 'get_theme_mod', 'wp_get_theme', 'get_template', 'get_stylesheet' );

	foreach ( nkc_scan_plugins() as $slug ) {
		$dir = WP_PLUGIN_DIR . '/' . $slug;
		if ( ! is_dir( $dir ) ) {
			continue;
		}
		$scanned[] = $slug;
		foreach ( nkc_php_files( $dir ) as $file ) {
			$code = @file_get_contents( $file ); // phpcs:ignore
			if ( false === $code || '' === $code ) {
				continue;
			}
			try {
				$tok = nkc_tokens( $code );
			} catch ( \Throwable $e ) {
				continue;
			}
			$rel = ltrim( str_replace( $root, '', wp_normalize_path( $file ) ), '/' );
			$ns  = '';
			$n   = count( $tok );
			$depth = 0;
			$class_stack = array();
			$pending_class = false;
			for ( $i = 0; $i < $n; $i++ ) {
				$t = $tok[ $i ];
				// brace / class-body tracking: functions declared inside a class are methods, not global functions
				if ( in_array( $t[0], array( T_CLASS, T_INTERFACE, T_TRAIT ), true ) && ( $i < 1 || T_DOUBLE_COLON !== $tok[ $i - 1 ][0] ) ) {
					$pending_class = true;
				} elseif ( '{' === $t[1] && 0 === $t[0] ) {
					$depth++;
					if ( $pending_class ) {
						$class_stack[] = $depth;
						$pending_class = false;
					}
				} elseif ( '}' === $t[1] && 0 === $t[0] ) {
					if ( $class_stack && end( $class_stack ) === $depth ) {
						array_pop( $class_stack );
					}
					$depth--;
				} elseif ( T_CURLY_OPEN === $t[0] || T_DOLLAR_OPEN_CURLY_BRACES === $t[0] ) {
					$depth++;
				}
				if ( T_NAMESPACE === $t[0] && isset( $tok[ $i + 1 ] ) && ( in_array( $tok[ $i + 1 ][0], $name_t, true ) ) ) {
					$ns = strtolower( ltrim( $tok[ $i + 1 ][1], '\\' ) );
					continue;
				}
				if ( T_NAMESPACE === $t[0] && isset( $tok[ $i + 1 ] ) && '{' === $tok[ $i + 1 ][1] ) {
					$ns = '';
					continue;
				}
				if ( T_FUNCTION === $t[0] ) {
					$j = $i + 1;
					if ( isset( $tok[ $j ] ) && '&' === $tok[ $j ][1] ) {
						$j++;
					}
					// a named top-level function (class methods are skipped)
					if ( ! $class_stack && isset( $tok[ $j ] ) && T_STRING === $tok[ $j ][0] ) {
						$defined[ strtolower( ( $ns ? $ns . '\\' : '' ) . $tok[ $j ][1] ) ] = true;
					}
					continue;
				}
				if ( ! in_array( $t[0], $name_t, true ) || ! isset( $tok[ $i + 1 ] ) || '(' !== $tok[ $i + 1 ][1] ) {
					continue;
				}
				$prev = $i > 0 ? $tok[ $i - 1 ] : array( 0, '', 0 );
				if ( in_array( $prev[0], array( T_FUNCTION, T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_NEW, T_CLASS, T_INTERFACE, T_TRAIT ), true ) || ( defined( 'T_NULLSAFE_OBJECT_OPERATOR' ) && T_NULLSAFE_OBJECT_OPERATOR === $prev[0] ) ) {
					continue;
				}
				$name = $t[1];
				$line = (int) $t[2];
				$lc   = strtolower( ltrim( $name, '\\' ) );

				if ( in_array( $lc, $path_fns, true ) ) {
					$info['theme_paths'][ $lc ][] = $rel . ':' . $line;
				}
				if ( in_array( $lc, $check_fns, true ) ) {
					$arg = isset( $tok[ $i + 2 ] ) && T_CONSTANT_ENCAPSED_STRING === $tok[ $i + 2 ][0] ? trim( $tok[ $i + 2 ][1], '\'"' ) : '';
					$info['theme_checks'][] = array( $lc . ( $arg ? "( '$arg' )" : '()' ), $rel . ':' . $line );
				}
				if ( in_array( $lc, array( 'add_action', 'add_filter' ), true ) && isset( $tok[ $i + 2 ] ) && T_CONSTANT_ENCAPSED_STRING === $tok[ $i + 2 ][0] ) {
					$info['listens'][ trim( $tok[ $i + 2 ][1], '\'"' ) ][] = $rel . ':' . $line;
				}
				if ( in_array( $lc, array( 'do_action', 'apply_filters', 'do_action_ref_array', 'apply_filters_ref_array' ), true ) && isset( $tok[ $i + 2 ] ) && T_CONSTANT_ENCAPSED_STRING === $tok[ $i + 2 ][0] ) {
					$info['fires'][ trim( $tok[ $i + 2 ][1], '\'"' ) ] = true;
				}
				if ( in_array( $lc, array( 'wp_enqueue_script', 'wp_register_script', 'wp_enqueue_style', 'wp_register_style' ), true ) ) {
					// dependency list: look for 'neuros…' / theme-looking handles inside the next ~40 tokens
					for ( $k = $i + 2; $k < min( $n, $i + 40 ) && ';' !== $tok[ $k ][1]; $k++ ) {
						if ( T_CONSTANT_ENCAPSED_STRING === $tok[ $k ][0] && preg_match( '/^[\'"](neuros[\w-]*|theme[\w-]*)[\'"]$/i', $tok[ $k ][1] ) && $k > $i + 4 ) {
							$info['deps'][] = array( trim( $tok[ $k ][1], '\'"' ), $rel . ':' . $line );
						}
					}
				}
				$calls[] = array( $name, $ns, $rel, $line );
			}
		}
	}

	// resolve calls
	$unresolved = array();
	foreach ( $calls as $c ) {
		list( $name, $ns, $rel, $line ) = $c;
		$lc    = strtolower( $name );
		$fq    = '\\' === $lc[0];
		$bare  = ltrim( $lc, '\\' );
		$cands = $fq || false !== strpos( $bare, '\\' ) ? array( $bare ) : array( $bare, ( $ns ? $ns . '\\' : '' ) . $bare );
		$ok    = false;
		foreach ( $cands as $cand ) {
			if ( isset( $defined[ $cand ] ) || function_exists( $cand ) ) {
				$ok = true;
				break;
			}
		}
		if ( ! $ok ) {
			$unresolved[ $bare ][] = $rel . ':' . $line;
		}
	}
	ksort( $unresolved );

	$orphan_hooks = array();
	foreach ( $info['listens'] as $hook => $where ) {
		if ( 0 === strpos( $hook, 'neuros' ) && ! isset( $info['fires'][ $hook ] ) ) {
			$orphan_hooks[ $hook ] = $where;
		}
	}
	return array(
		'plugins'      => $scanned,
		'files'        => count( $calls ),
		'unresolved'   => $unresolved,
		'theme_paths'  => $info['theme_paths'],
		'theme_checks' => $info['theme_checks'],
		'orphan_hooks' => $orphan_hooks,
		'deps'         => $info['deps'],
	);
}

/** Widget types used in saved Elementor data: type => array( post ids ). Read-only. */
function nkc_widget_usage() {
	global $wpdb;
	$ids   = $wpdb->get_col( "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key='_elementor_data' AND (meta_value LIKE '%neuros\\_%' OR meta_value LIKE '%lakit%')" ); // phpcs:ignore
	$usage = array();
	$walk  = function ( $els, $pid ) use ( &$walk, &$usage ) {
		foreach ( (array) $els as $el ) {
			if ( ! is_array( $el ) ) {
				continue;
			}
			if ( ! empty( $el['widgetType'] ) ) {
				$usage[ $el['widgetType'] ][ $pid ] = ( $usage[ $el['widgetType'] ][ $pid ] ?? 0 ) + 1;
			}
			if ( ! empty( $el['elements'] ) ) {
				$walk( $el['elements'], $pid );
			}
		}
	};
	foreach ( $ids as $pid ) {
		$json = get_post_meta( (int) $pid, '_elementor_data', true );
		$data = is_string( $json ) ? json_decode( $json, true ) : $json;
		if ( is_array( $data ) ) {
			$walk( $data, (int) $pid );
		}
	}
	ksort( $usage );
	return $usage;
}
