<?php
/**
 * Installs WordPress for running the tests and loads WordPress and the test libraries.
 */


/*
 * Globalize some WordPress variables, because PHPUnit loads this file inside a function
 * See: https://github.com/sebastianbergmann/phpunit/issues/325
 */
global $wpdb, $current_site, $current_blog, $wp_rewrite, $shortcode_tags, $wp, $phpmailer, $wp_theme_directories;

if (! getenv('WP_TESTS_CONFIG_PATH')) {
    putenv('WP_TESTS_CONFIG_PATH='.__DIR__.'/wp-tests-config.php');
}

require_once getenv('WP_TESTS_CONFIG_PATH');
require_once __DIR__.'/includes/functions.php';

tests_reset__SERVER();

if (! defined('WP_TESTS_FORCE_KNOWN_BUGS')) {
    define('WP_TESTS_FORCE_KNOWN_BUGS', false);
}

// Cron tries to make an HTTP request to the blog, which always fails, because tests are run in CLI mode only
define('DISABLE_WP_CRON', true);

define('WP_MEMORY_LIMIT', -1);
define('WP_MAX_MEMORY_LIMIT', -1);

define('REST_TESTS_IMPOSSIBLY_HIGH_NUMBER', 99999999);

$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

// Override the PHPMailer
require_once __DIR__.'/includes/mock-mailer.php';
$phpmailer = new MockPHPMailer(true);

if (! defined('WP_DEFAULT_THEME')) {
    define('WP_DEFAULT_THEME', 'default');
}

$wp_theme_directories = [__DIR__.'/themedir'];

$GLOBALS['_wp_die_disabled'] = false;
// Allow tests to override wp_die
tests_add_filter('wp_die_handler', '_wp_die_handler_filter');

// Preset WordPress options defined in bootstrap file.
// Used to activate themes, plugins, as well as  other settings.
if (isset($GLOBALS['wp_tests_options'])) {
    function wp_tests_options($value)
    {
        $key = substr(current_filter(), strlen('pre_option_'));

        return $GLOBALS['wp_tests_options'][$key];
    }

    foreach (array_keys($GLOBALS['wp_tests_options']) as $key) {
        tests_add_filter('pre_option_'.$key, 'wp_tests_options');
    }
}

// Load WordPress
require_once ABSPATH.'/wp-settings.php';

// Delete any default posts & related data
// _delete_all_posts();

require __DIR__.'/includes/utils.php';
