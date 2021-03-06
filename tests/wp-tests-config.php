<?php

foreach ([
    'APP_DEBUG' => true,
    'DB_HOST' => 'localhost',
    'DB_DATABASE' => 'wordpress',
    'DB_USERNAME' => 'root',
    'DB_PASSWORD' => '',
    'DB_PREFIX' => 'wptests_',
] as $key => $value) {
    if (! getenv($key)) {
        putenv("$key=$value");
    }
}

/* Path to the WordPress codebase you'd like to test. Add a forward slash in the end. */
define('ABSPATH', __DIR__.'/../../../johnpbloch/wordpress-core/');

/*
 * Path to the theme to test with.
 *
 * The 'default' theme is symlinked from test/phpunit/data/themedir1/default into
 * the themes directory of the WordPress installation defined above.
 */
define('WP_DEFAULT_THEME', 'default');

// Test with multisite enabled.
// Alternatively, use the tests/phpunit/multisite.xml configuration file.
// define( 'WP_TESTS_MULTISITE', true );

// Force known bugs to be run.
// Tests with an associated Trac ticket that is still open are normally skipped.
// define( 'WP_TESTS_FORCE_KNOWN_BUGS', true );

// Test with WordPress debug mode (default).
define('WP_DEBUG', getenv('APP_DEBUG') === 'true' ? true : false);

// ** MySQL settings ** //

// This configuration file will be used by the copy of WordPress being tested.
// wordpress/wp-config.php will be ignored.

// WARNING WARNING WARNING!
// These tests will DROP ALL TABLES in the database with the prefix named below.
// DO NOT use a production database or one that is shared with something else.

define('DB_HOST', getenv('DB_HOST'));
define('DB_NAME', getenv('DB_DATABASE'));
define('DB_USER', getenv('DB_USERNAME'));
define('DB_PASSWORD', getenv('DB_PASSWORD'));
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

// Only numbers, letters, and underscores please!
$table_prefix = getenv('DB_PREFIX');

define('WP_TESTS_DOMAIN', 'example.org');
define('WP_TESTS_EMAIL', 'admin@example.org');
define('WP_TESTS_TITLE', 'Test Blog');

define('WP_PHP_BINARY', 'php');

define('WPLANG', '');
