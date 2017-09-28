<?php 

namespace LumenPress\Testing;

trait WordPressTestCase
{
    /**
     * Modify WordPress's query internals as if a given URL has been requested.
     *
     * @param string $url The URL for the request.
     */
    public function setWpQueryVars($url)
    {
        add_filter('pre_handle_404', '__return_true');
        // note: the WP and WP_Query classes like to silently fetch parameters
        // from all over the place (globals, GET, etc), which makes it tricky
        // to run them more than once without very carefully clearing everything
        $_GET = $_POST = [];
        foreach (['query_string', 'id', 'postdata', 'authordata', 'day', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages', 'pagenow'] as $v) {
            if (isset($GLOBALS[$v])) unset($GLOBALS[$v]);
        }
        $parts = parse_url($url);
        if (isset($parts['scheme'])) {
            $req = isset( $parts['path'] ) ? $parts['path'] : '';
            if (isset($parts['query'])) {
                $req .= '?' . $parts['query'];
                // parse the url query vars into $_GET
                parse_str($parts['query'], $_GET);
            }
        } else {
            $req = $url;
        }
        if (! isset($parts['query'])) {
            $parts['query'] = '';
        }

        $_SERVER['REQUEST_URI'] = $req;
        unset($_SERVER['PATH_INFO']);

        $this->flushCache();
        unset($GLOBALS['wp_query'], $GLOBALS['wp_the_query']);
        $GLOBALS['wp_the_query'] = new \WP_Query();
        $GLOBALS['wp_the_query']->is_404 = false;
        $GLOBALS['wp_query'] = $GLOBALS['wp_the_query'];

        $public_query_vars  = $GLOBALS['wp']->public_query_vars;
        $private_query_vars = $GLOBALS['wp']->private_query_vars;

        $GLOBALS['wp'] = new \WP();
        $GLOBALS['wp']->public_query_vars  = $public_query_vars;
        $GLOBALS['wp']->private_query_vars = $private_query_vars;

        $this->cleanupQueryVars();

        $GLOBALS['wp']->main($parts['query']);

        add_filter('pre_handle_404', '__return_false');
    }

    public function setPermalinkStructure($structure = '')
    {
        global $wp_rewrite;

        $wp_rewrite->init();
        $wp_rewrite->set_permalink_structure( $structure );
        $wp_rewrite->flush_rules();
    }

    protected function flushCache()
    {
        global $wp_object_cache;
        $wp_object_cache->group_ops = array();
        $wp_object_cache->stats = array();
        $wp_object_cache->memcache_debug = array();
        $wp_object_cache->cache = array();
        if ( method_exists( $wp_object_cache, '__remoteset' ) ) {
            $wp_object_cache->__remoteset();
        }
        wp_cache_flush();
        wp_cache_add_global_groups( array( 'users', 'userlogins', 'usermeta', 'user_meta', 'useremail', 'userslugs', 'site-transient', 'site-options', 'blog-lookup', 'blog-details', 'rss', 'global-posts', 'blog-id-cache', 'networks', 'sites', 'site-details' ) );
        wp_cache_add_non_persistent_groups( array( 'comment', 'counts', 'plugins' ) );
    }

    protected function cleanupQueryVars() {
        // clean out globals to stop them polluting wp and wp_query
        foreach ( $GLOBALS['wp']->public_query_vars as $v )
            unset( $GLOBALS[$v] );

        foreach ( $GLOBALS['wp']->private_query_vars as $v )
            unset( $GLOBALS[$v] );

        foreach ( get_taxonomies( array() , 'objects' ) as $t ) {
            if ( $t->publicly_queryable && ! empty( $t->query_var ) )
                $GLOBALS['wp']->add_query_var( $t->query_var );
        }

        foreach ( get_post_types( array() , 'objects' ) as $t ) {
            if ( is_post_type_viewable( $t ) && ! empty( $t->query_var ) )
                $GLOBALS['wp']->add_query_var( $t->query_var );
        }
    }
}