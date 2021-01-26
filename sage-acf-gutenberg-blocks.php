
<?php
/**
 * Gutenberg Blocks for Roots/Sage theme 10
 * Version: 1.0
 * Author: Łukasz Górski
 */

if( ! class_exists('ACF') ) {
    if (defined('WP_ENV') && WP_ENV == 'production') {
        add_filter('acf/settings/show_admin', '__return_false');
    }

    add_filter('acf/init', function ($path) {
        $path = get_stylesheet_directory() . '/resources/views/blocks';
        if (!is_dir($path)) {
            mkdir($path);
        }
        return $path;
    });


}






