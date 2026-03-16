<?php
/**
 * Plugin Name: Panel Weselny
 * Description: Panel weselny dla klientów WooCommerce.
 * Version: 1.0.0
 * Author: ZIGS
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'panel-klienta.php';
require_once plugin_dir_path(__FILE__) . 'panel-gosci.php';


/**
 * Rejestracja CPT Wesela
 */

function wp_weselny_panel_register_cpt() {

    register_post_type( 'wesela', array(
        'labels' => array(
            'name' => 'Wesela',
            'singular_name' => 'Wesele',
        ),
        'public' => true,
        'show_ui' => true,
        'menu_icon' => 'dashicons-heart',
        'supports' => array( 'title', 'editor' ),
        'rewrite' => array(
            'slug' => 'wesele',
            'with_front' => false
        ),
    ));

}

add_action( 'init', 'wp_weselny_panel_register_cpt' );


/**
 * Tworzenie wpisu wesela po zakupie
 */

function wp_weselny_panel_create_wedding_post( $order_id ) {

    $order = wc_get_order( $order_id );
    $user_id = $order->get_user_id();

    foreach ( $order->get_items() as $item ) {

        if ( $item->get_product_id() == 19 ) {

            $existing = get_posts(array(
                'post_type' => 'wesela',
                'meta_key' => 'user_id',
                'meta_value' => $user_id,
                'posts_per_page' => 1
            ));

            if ( ! $existing ) {

                $post_id = wp_insert_post(array(
                    'post_type' => 'wesela',
                    'post_status' => 'publish',
                    'post_title' => 'Wesele użytkownika ' . $user_id,
                    'post_content' => 'Witaj w panelu weselnym',
                ));

                update_post_meta( $post_id, 'user_id', $user_id );

            }

        }

    }

}

add_action( 'woocommerce_order_status_processing', 'wp_weselny_panel_create_wedding_post' );
add_action( 'woocommerce_order_status_completed', 'wp_weselny_panel_create_wedding_post' );


/**
 * Loader modułów
 */

$modules = plugin_dir_path(__FILE__) . 'modules/*';

foreach ( glob($modules) as $module ) {

    foreach ( glob($module.'/*-panel.php') as $file ) {
        require_once $file;
    }

    foreach ( glob($module.'/*-guests.php') as $file ) {
        require_once $file;
    }

}