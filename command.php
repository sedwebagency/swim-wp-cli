<?php

use function WP_CLI\Utils\make_progress_bar;

class Swim_WP_CLI extends WP_CLI_Command {
	/**
	 * Make the website accessible via www.
	 *
	 * ## OPTIONS
	 *
	 * [--reverse=<bool>]
	 * : Do to-non-www instead.
	 *
	 * ## EXAMPLES
	 *
	 *     wp swim to-www
	 *
	 * @subcommand to-www
	 */
	public function to_www( array $args = [], array $assoc_args = [] ) {
		// todo run autoperm
		WP_CLI::warning( "Implement autoperm here..." );

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

		if ( isset( $assoc_args['reverse'] ) && 'true' === $assoc_args['reverse'] ) {
			// OPERATION: www => non-www
			$count = WP_CLI::runcommand( "search-replace 'www.$source_domain' '$source_domain' $__skip_all --precise --all-tables-with-prefix --format=count", $options );
			WP_CLI::success( "Made $count replacements." );
		} else {
			// OPERATION: non-www => www

			// prepare
			WP_CLI::debug( "Avoid multiple www (www.www.)..." );
			WP_CLI::runcommand( "search-replace 'www.$source_domain' '$source_domain' $__skip_all --precise --all-tables-with-prefix --format=count", $options );

			$count = WP_CLI::runcommand( "search-replace '$source_domain' 'www.$source_domain' $__skip_all --precise --all-tables-with-prefix --format=count", $options );
			WP_CLI::debug( "Made $count replacements." );

			// complete
			WP_CLI::debug( "Avoid emails www (@www)..." );
			WP_CLI::runcommand( "search-replace '@www.$source_domain' '@$source_domain' $__skip_all --precise --all-tables-with-prefix --format=count", $options );
		}

		// todo run clean-cache
		WP_CLI::warning( "Implement cache cleanup here..." );

		WP_CLI::debug( "Clean transients..." );
		WP_CLI::runcommand( "transient delete --all $__skip_all", $options );

		WP_CLI::debug( "Flush cache..." );
		WP_CLI::runcommand( "cache flush $__skip_all", $options );

		// WP_CLI::runcommand( "rocket clean --skip-themes" );

		WP_CLI::success( "Done." );
	}

	/**
	 * Make the website accessible via https.
	 *
	 * ## EXAMPLES
	 *
	 *     wp swim to-https
	 *
	 * @subcommand to-https
	 */
	public function to_https( array $args = [], array $assoc_args = [] ) {
		WP_CLI::error( "To be implemented." );
	}
}

WP_CLI::add_command( 'swim', 'Swim_WP_CLI' );
