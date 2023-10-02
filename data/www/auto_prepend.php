<?php
/**
 * Handles loading auto_prepend files from the individual sub-sites under data/www/[SITE](/htdocs)/ automatically using
 * the current working directory.
 *
 * @return void
 */
function uconnect_auto_prepend() {
	$current_working_directory = getcwd();
	/**
	 * /data/www/ is mapped to /shared/httpd/ in the PHP container.
	 */
	if ( str_starts_with( $current_working_directory, '/shared/httpd/' ) ) {
		$cwd_parts = explode( '/', $current_working_directory );
		if ( count( $cwd_parts ) >= 3 ) {
			$path = '/' . implode( '/', array_slice( $cwd_parts, 0, 4 ) ) . '/';
			if ( file_exists( $path . 'auto_prepend.php' ) ) {
				require_once $path . 'auto_prepend.php';
			}
			if ( file_exists( $path . 'htdocs/auto_prepend.php' ) ) {
				require_once $path . 'htdocs/auto_prepend.php';
			}
		}
	}
}

uconnect_auto_prepend();
