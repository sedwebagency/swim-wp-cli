<?php

use function WP_CLI\Utils\make_progress_bar;

class Swim_WP_CLI extends WP_CLI_Command {
	// todo move the version to the "package meta" docblock
	const VERSION = '1.0.1';

	/**
	 * A test which always gives success and the current version.
	 *
	 * ## EXAMPLES
	 *
	 *     wp swim test
	 *
	 * @subcommand test
	 */
	public function test( array $args = [], array $assoc_args = [] ) {
		$current_version = self::VERSION;

		WP_CLI::success( "Success. Installed version $current_version." );
	}

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

		WP_CLI::debug( "Cache cleanup..." );
		WP_CLI::runcommand( "swim cache-clean", $options );

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

		// from http://source_domain...
		$count = WP_CLI::runcommand( "search-replace 'http://$source_domain' 'https://$source_domain' $__skip_all --precise --all-tables-with-prefix --format=count", $options );
		WP_CLI::debug( "Made $count replacements from non-www to non-www (ssl)." );

		// from http://www.source_domain ...
		WP_CLI::runcommand( "search-replace 'http://www.$source_domain' 'https://www.$source_domain' $__skip_all --precise --all-tables-with-prefix --format=count", $options );
		WP_CLI::debug( "Made $count replacements from www to www (ssl)." );

		WP_CLI::debug( "Cache cleanup..." );
		WP_CLI::runcommand( "swim cache-clean", $options );

		WP_CLI::success( "Done." );
	}

	/**
	 * Update all
	 *
	 * ## EXAMPLES
	 *
	 *     wp swim update-all
	 *
	 * @subcommand update-all
	 */
	public function update_all( array $args = [], array $assoc_args = [] ) {
		// subcommand options
		$options = array(
			'return'     => true,   // Return 'STDOUT'; use 'all' for full object.
			'parse'      => 'json', // Parse captured STDOUT to JSON array.
			'launch'     => false,  // Reuse the current process.
			'exit_error' => true,   // Halt script execution on error.
		);

		WP_CLI::debug( "Update core..." );
		WP_CLI::runcommand( "core update", $options );

		WP_CLI::debug( "Update themes..." );
		WP_CLI::runcommand( "theme update --all", $options );

		WP_CLI::debug( "Update plugins..." );
		WP_CLI::runcommand( "plugin update --all", $options );

		WP_CLI::debug( "Update core languages..." );
		WP_CLI::runcommand( "language core update", $options );

		WP_CLI::debug( "Update themes languages..." );
		WP_CLI::runcommand( "language theme update --all", $options );

		WP_CLI::debug( "Update plugins languages..." );
		WP_CLI::runcommand( "language plugin update --all", $options );

		WP_CLI::debug( "Cache cleanup..." );
		WP_CLI::runcommand( "swim cache-clean", $options );

		WP_CLI::success( "Done." );
	}

	/**
	 * Cache clean
	 *
	 * ## EXAMPLES
	 *
	 *     wp swim cache-clean
	 *
	 * @subcommand cache-clean
	 */
	public function cache_clean( array $args = [], array $assoc_args = [] ) {
		// todo complete implementation

		// subcommand options
		$options = array(
			'return'     => true,   // Return 'STDOUT'; use 'all' for full object.
			'parse'      => 'json', // Parse captured STDOUT to JSON array.
			'launch'     => false,  // Reuse the current process.
			'exit_error' => true,   // Halt script execution on error.
		);

		WP_CLI::debug( "Clean transients..." );
		WP_CLI::runcommand( "transient delete --all", $options );

		WP_CLI::debug( "Flush cache..." );
		WP_CLI::runcommand( "cache flush", $options );

		// WP_CLI::runcommand( "rocket clean --skip-themes" );

		WP_CLI::success( "Done." );
	}

	/**
	 * Autoperm
	 *
	 * ## EXAMPLES
	 *
	 *     wp swim autoperm
	 *
	 * @subcommand autoperm
	 */
	public function autoperm( array $args = [], array $assoc_args = [] ) {
		$commands = array();

		$wordpress_path = ABSPATH;

		$linux_user  = ltrim( $_SERVER['home'], '/home/' );
		$linux_group = $linux_user;
		var_dump( $_SERVER, $_SERVER['home'], $linux_user );

		if ( empty( $wordpress_path ) || empty( $linux_user ) ) {
			WP_CLI::error( "Non riesco ad ottenere il percorso del sito o l'utente linux" );
			exit;
		}

		// fix owner
		$commands[] = "chown -R $linux_user:$linux_group $wordpress_path";

		// fix perms
		$commands[] = "find $wordpress_path -type d -exec chmod 755 {} \;";
		$commands[] = "find $wordpress_path -type f -exec chmod 644 {} \;";

		foreach ( $commands as $command ) {
			WP_CLI::line( $command );
		}

		WP_CLI::success( "Done." );
	}
}

WP_CLI::add_command( 'swim', 'Swim_WP_CLI' );
