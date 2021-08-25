<?php

use function WP_CLI\Utils\make_progress_bar;

class Swim_WP_CLI extends WP_CLI_Command {
	// todo move the version to the "package meta" docblock
	const VERSION = '1.3.0';

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
	 * Update core, plugins, themes and translations
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
	 * Automatically clean all caches (wp, opcache, plugins, object-cache...)
	 *
	 * ## EXAMPLES
	 *
	 *     wp swim cache-clean
	 *
	 * @subcommand cache-clean
	 */
	public function cache_clean( array $args = [], array $assoc_args = [] ) {
		global $wpdb;

		// opcache
		if ( function_exists( 'opcache_reset' ) && ini_get( 'opcache.enable' ) ) {
			opcache_reset();
		}

		// transients
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_%' OR option_name LIKE '\_site\_transient\_%'" );

		// memcached (which stores transients as well, if active)
		wp_cache_flush();

		// WP Rocket
		if ( function_exists( 'rocket_clean_domain' ) ) {
			rocket_clean_domain();
		}

		// W3 Total Cache
		if ( function_exists( 'w3tc_flush_all' ) ) {
			w3tc_flush_all();
		}

		// WP Super Cache
		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			wp_cache_clear_cache();
		}

		// LiteSpeed Cache
		if ( method_exists( 'LiteSpeed_Cache_API', 'purge_all' ) ) {
			LiteSpeed_Cache_API::purge_all();
		}

		// Endurance
		if ( class_exists( 'Endurance_Page_Cache' ) ) {
			$epc = new Endurance_Page_Cache;
			$epc->purge_all();
		}

		// WPEngine
		if ( class_exists( 'WpeCommon' ) && method_exists( 'WpeCommon', 'purge_memcached' ) ) {
			WpeCommon::purge_memcached();
			WpeCommon::purge_varnish_cache();
		}

		// Siteground
		if ( class_exists( 'SG_CachePress_Supercacher' ) && method_exists( 'SG_CachePress_Supercacher', 'purge_cache' ) ) {
			SG_CachePress_Supercacher::purge_cache( true );
		}

		// Siteground
		if ( class_exists( 'SiteGround_Optimizer\Supercacher\Supercacher' ) ) {
			SiteGround_Optimizer\Supercacher\Supercacher::purge_cache();
		}

		// Cache Enabler
		if ( class_exists( 'Cache_Enabler' ) && method_exists( 'Cache_Enabler', 'clear_total_cache' ) ) {
			Cache_Enabler::clear_total_cache();
		}

		// Pagely
		if ( class_exists( 'PagelyCachePurge' ) && method_exists( 'PagelyCachePurge', 'purgeAll' ) ) {
			PagelyCachePurge::purgeAll();
		}

		// Autoptimize
		if ( class_exists( 'autoptimizeCache' ) && method_exists( 'autoptimizeCache', 'clearall' ) ) {
			autoptimizeCache::clearall();
		}

		// Comet cache
		if ( class_exists( 'comet_cache' ) && method_exists( 'comet_cache', 'clear' ) ) {
			comet_cache::clear();
		}

		// WP Fastest Cache
		if ( function_exists( 'wpfc_clear_all_cache' ) ) {
			wpfc_clear_all_cache( true );
		}

		// Swift
		if ( is_callable( array( 'Swift_Performance_Cache', 'clear_all_cache' ) ) ) {
			Swift_Performance_Cache::clear_all_cache();
		}

		// Hummingbird Cache
		if ( is_callable( array( 'Hummingbird\WP_Hummingbird', 'flush_cache' ) ) ) {
			Hummingbird\WP_Hummingbird::flush_cache( true, false );
		}

		// Cloudflare CDN
		if ( class_exists( '\CF\WordPress\Hooks' ) ) {
			$cloudflareHooks = new \CF\WordPress\Hooks();
			$cloudflareHooks->purgeCacheEverything();
		}

		WP_CLI::success( "Done." );
	}

	/**
	 * Automatically apply own/perms to the root
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

		$linux_user  = ltrim( $_SERVER['HOME'], '/home/' );
		$linux_group = $linux_user;

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
			WP_CLI::debug( $command );
			exec( $command );
		}

		WP_CLI::success( "Done." );
	}
}

WP_CLI::add_command( 'swim', 'Swim_WP_CLI' );
