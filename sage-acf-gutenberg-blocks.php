<?php
/**
 * Gutenberg Blocks for Roots/Sage theme 10
 * Version: 1.0.6
 * Author: Łukasz Górski / TotalDigital
 */

namespace App;

if ( function_exists( 'add_action' ) ) {
    add_action( 'after_setup_theme', function () {

        if ( ! function_exists( 'acf_register_block_type' ) ) {
            return;
        }
        if ( ! function_exists( 'add_filter' ) ) {
            return;
        }
        if ( ! function_exists( 'add_action' ) ) {
            return;
        }

        add_filter( 'sage-acf-gutenberg-blocks-templates', function () {
            return [ 'views/blocks' ];
        } );
    }, 30 );
}

if ( function_exists( 'add_action' ) ) {
    add_action( 'acf/init', function () {
        global $sage_error;
        $directories = apply_filters( 'sage-acf-gutenberg-blocks-templates', [] );
        foreach ( $directories as $directory ) {
            $dir         = \Roots\resource_path( 'views/blocks' );
            $blocks_path = ( 'resources/views/blocks' );

            if ( ! file_exists( $dir ) ) {
                return;
            }

            $blocks_directory = new \DirectoryIterator( $dir );

            foreach ( $blocks_directory as $directory ) {
                if ( ! $directory->isDot() ) {

                    $block_name = $directory->getFilename();
                    $block_path = "{$dir}/{$block_name}";
                    $file       = "{$block_path}/{$block_name}.blade.php";
                    $config     = "{$block_path}/config.php";
                    $theme_url  = get_template_directory_uri();
                    $theme_path = get_template_directory();
                    $dist_css   = 'public/styles/blocks';
                    $dist_js    = 'public/scripts/blocks';

                    $file_headers = get_file_data( $file, [
                        'title'    => 'Title',
                        'category' => 'Category',
                    ] );

                    $options = [
                        'name'            => $block_name,
                        'title'           => __( $file_headers['title'] ),
                        'category'        => $file_headers['category'],
                        'render_callback' => __NAMESPACE__ . '\\blocks_callback',
                        'supports'        => [
                            'align' => [ 'full', 'center', 'wide' ],
                        ],
                        'align'           => empty( $file_headers['align'] ) ? 'full' : $file_headers['align'],
                        'theme_url' => $theme_url,
                        'dist_css' => $dist_css,
                        'dist_js' => $dist_js,
                        'theme_path' => $theme_path,
                        'block_name' => $block_name,
                        'example'         => [
                            'attributes' => [
                                'mode' => 'preview',
                                'data' => [
                                    'preview_image' => "{$theme_url}/{$blocks_path}/{$block_name}/screenshot.png",
                                    "is_preview"    => 1
                                ],
                            ]
                        ],
                    ];


                    \acf_register_block_type( apply_filters( "sage/blocks/$block_name/register-data", $options ) );

                    if ( ! file_exists( $config ) ) {
                        continue;
                    }
                    require_once( $config );
                    acf_add_local_field_group( [
                        "key"      => "group_{$block_name}",
                        "title"    => "BLOCK: {$file_headers['title']}",
                        "fields"   => $fields,
                        'location' => [ [ [ 'param' => 'block', 'operator' => '==', 'value' => "acf/{$block_name}" ] ] ],
                    ] );

                }
            }
        }
    } );
}

function blocks_callback( $block, $content = '', $is_preview = false, $post_id = 0 ) {

    $slug = str_replace( 'acf/', '', $block['name'] );

    $block['post_id']       = $post_id;
    $block['preview']       = $is_preview;
    $block['content']       = $content;
    $block['slug']          = $slug;
    $block['anchor']        = isset( $block['anchor'] ) ? $block['anchor'] : '';
    $block['classes']       = [
        $slug,
        $block['preview'] ? 'is-preview' : null,
        'align' . $block['align']
    ];
    $block['preview_image'] = $block['example']['attributes']['data']['preview_image'];

    $block            = apply_filters( "sage/blocks/$slug/data", $block );
    $block['classes'] = implode( ' ', array_filter( $block['classes'] ) );

    $directories = apply_filters( 'sage-acf-gutenberg-blocks-templates', [] );

    foreach ( $directories as $directory ) {
        $view = ltrim( $directory, 'views/' ) . '/' . $slug . '/' . $slug;
        if ( \Roots\view()->exists( $view ) ) {
            echo \Roots\view( $view, [ 'block' => $block ] );
        }
    }
}
