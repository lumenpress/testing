<?php

/**
 * Laravel - A PHP Framework For Web Artisans.
 */
if (! defined('LP_THEME_DIR')) {
    return;
}

putenv('APP_DEBUG='.(WP_DEBUG ? 'true' : 'false'));
putenv('DB_CONNECTION=mysql');
putenv('DB_HOST='.DB_HOST);
putenv('DB_DATABASE='.DB_NAME);
putenv('DB_USERNAME='.DB_USER);
putenv('DB_PASSWORD='.DB_PASSWORD);
putenv('DB_PREFIX='.$GLOBALS['table_prefix']);
putenv('APP_TIMEZONE='.get_option('timezone_string') ?: 'UTC');

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels nice to relax.
|
*/

if (file_exists(LP_THEME_DIR.'bootstrap/autoload.php')) {
    require LP_THEME_DIR.'bootstrap/autoload.php';
}

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require_once LP_THEME_DIR.'bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

add_action('template_redirect', function () use ($app) {
    if (is_robots() || is_feed() || is_trackback()) {
        return;
    }

    if (stripos($app->version(), 'Lumen') !== false) {
        $app->run();

        return;
    }

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );

    $response->send();
    $kernel->terminate($request, $response);
}, 9999);
