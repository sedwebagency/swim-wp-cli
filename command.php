<?php

use function WP_CLI\Utils\make_progress_bar;

class Swim_WP_CLI extends WP_CLI_Command {
	/**
	 * Make the website accessible via www.
	 *
	 * ## EXAMPLES
	 *
	 *     wp swim to-www
	 *
	 * @subcommand to-www
	 */
	public function to_www( array $args = [], array $assoc_args = [] ) {
		// todo run autoperm first

		$source_domain = parse_url( get_site_url(), PHP_URL_HOST );
		$source_domain = ltrim( $source_domain, 'www.' );

		// subcommand options
		$options = array(
			'return'     => true,   // Return 'STDOUT'; use 'all' for full object.
			'parse'      => 'json', // Parse captured STDOUT to JSON array.
			'launch'     => false,  // Reuse the current process.
			'exit_error' => true,   // Halt script execution on error.
		);

		// subcommand helpers
		$__skip_all = '--skip-plugins --skip-themes';

		// avoid www.www. ...
		WP_CLI::runcommand( "search-replace 'www.$source_domain' '$source_domain' $__skip_all --precise --all-tables-with-prefix", $options );

		// do it
		WP_CLI::runcommand( "search-replace '$source_domain' 'www.$source_domain' $__skip_all --precise --all-tables-with-prefix", $options );
		WP_CLI::runcommand( "search-replace '@www.$source_domain' '@$source_domain' $__skip_all --precise --all-tables-with-prefix", $options );

		// clean caches
		// todo run clean-cache
		WP_CLI::runcommand( "transient delete --all $__skip_all", $options );
		WP_CLI::runcommand( "cache flush $__skip_all", $options );
		// WP_CLI::runcommand( "rocket clean --skip-themes" );
	}
}

WP_CLI::add_command( 'swim', 'Swim_WP_CLI' );
