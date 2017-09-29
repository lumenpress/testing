<?php

/**
 * Installs WordPress for running the tests and loads WordPress and the test libraries
 */

if (! defined('WP_TESTS_CONFIG_FILE_PATH')) {
	echo "ERROR: wp-tests-config.php is missing! Please use wp-tests-config-sample.php to create a config file.\n";
	exit( 1 );
}

if (! is_readable(WP_TESTS_CONFIG_FILE_PATH) ) {
	echo "ERROR: wp-tests-config.php is missing! Please use wp-tests-config-sample.php to create a config file.\n";
	exit( 1 );
}

/*
 * Globalize some WordPress variables, because PHPUnit loads this file inside a function
 * See: https://github.com/sebastianbergmann/phpunit/issues/325
 */
global $wpdb, $current_site, $current_blog, $wp_rewrite, $shortcode_tags, $wp, $phpmailer, $wp_theme_directories;

require_once WP_TESTS_CONFIG_FILE_PATH;
require_once dirname( __FILE__ ) . '/functions.php';

tests_reset__SERVER();

define( 'WP_TESTS_TABLE_PREFIX', $table_prefix );
define( 'DIR_TESTDATA', dirname( __FILE__ ) . '/../data' );

define( 'WP_LANG_DIR', DIR_TESTDATA . '/languages' );

if ( ! defined( 'WP_TESTS_FORCE_KNOWN_BUGS' ) )
	define( 'WP_TESTS_FORCE_KNOWN_BUGS', false );

// Cron tries to make an HTTP request to the blog, which always fails, because tests are run in CLI mode only
define( 'DISABLE_WP_CRON', true );

define( 'WP_MEMORY_LIMIT', -1 );
define( 'WP_MAX_MEMORY_LIMIT', -1 );

define( 'REST_TESTS_IMPOSSIBLY_HIGH_NUMBER', 99999999 );

$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

// Should we run in multisite mode?
$multisite = '1' == getenv( 'WP_MULTISITE' );
$multisite = $multisite || ( defined( 'WP_TESTS_MULTISITE') && WP_TESTS_MULTISITE );
$multisite = $multisite || ( defined( 'MULTISITE' ) && MULTISITE );

// Override the PHPMailer
require_once( dirname( __FILE__ ) . '/mock-mailer.php' );
$phpmailer = new MockPHPMailer( true );

if ( ! defined( 'WP_DEFAULT_THEME' ) ) {
	define( 'WP_DEFAULT_THEME', 'default' );
}
$wp_theme_directories = array( DIR_TESTDATA . '/themedir1' );

system( WP_PHP_BINARY . ' ' . escapeshellarg( dirname( __FILE__ ) . '/install.php' ) . ' ' . escapeshellarg( WP_TESTS_CONFIG_FILE_PATH ) . ' ' . $multisite );

// if ( $multisite ) {
// 	echo "Running as multisite..." . PHP_EOL;
// 	defined( 'MULTISITE' ) or define( 'MULTISITE', true );
// 	defined( 'SUBDOMAIN_INSTALL' ) or define( 'SUBDOMAIN_INSTALL', false );
// 	$GLOBALS['base'] = '/';
// } else {
// 	echo "Running as single site... To run multisite, use -c tests/phpunit/multisite.xml" . PHP_EOL;
// }
// unset( $multisite );

$GLOBALS['_wp_die_disabled'] = false;
// Allow tests to override wp_die
tests_add_filter( 'wp_die_handler', '_wp_die_handler_filter' );

// Preset WordPress options defined in bootstrap file.
// Used to activate themes, plugins, as well as  other settings.
if(isset($GLOBALS['wp_tests_options'])) {
	function wp_tests_options( $value ) {
		$key = substr( current_filter(), strlen( 'pre_option_' ) );
		return $GLOBALS['wp_tests_options'][$key];
	}

	foreach ( array_keys( $GLOBALS['wp_tests_options'] ) as $key ) {
		tests_add_filter( 'pre_option_'.$key, 'wp_tests_options' );
	}
}

// Load WordPress
require_once ABSPATH . '/wp-settings.php';

// Delete any default posts & related data
_delete_all_posts();

require dirname( __FILE__ ) . '/utils.php';
