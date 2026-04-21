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
 * 🔥 GENERATOR KODU
 */
function weselny_generate_code($length = 7){
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
}


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
 * 🔥 Tworzenie wpisu wesela (LOSOWY SLUG)
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

                /* 🔥 GENERUJEMY UNIKALNY KOD */
                do {
                    $code = weselny_generate_code();
                    $exists = get_page_by_path($code, OBJECT, 'wesela');
                } while($exists);

                $post_id = wp_insert_post(array(
                    'post_type'   => 'wesela',
                    'post_status' => 'publish',
                    'post_title'  => $code,
                    'post_name'   => $code, // 🔥 slug = kod
                    'post_content'=> '',
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


/**
 * STYLE
 */
function weselny_panel_styles(){

    wp_enqueue_style(
        'weselny-style',
        plugin_dir_url(__FILE__) . 'assets/css/style.css',
        [],
        '1.0'
    );

}
add_action('wp_enqueue_scripts','weselny_panel_styles');


/**
 * PARTICLES
 */
function weselny_particles_scripts(){

    if(get_post_type() === 'wesela' && is_singular('wesela')){

        wp_enqueue_script(
            'particles-js',
            'https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js',
            [],
            null,
            true
        );

        wp_enqueue_script(
            'weselny-particles-init',
            plugin_dir_url(__FILE__) . 'assets/js/particles-init.js',
            ['particles-js'],
            '1.0',
            true
        );

        wp_enqueue_script(
            'weselny-slide',
            plugin_dir_url(__FILE__) . 'assets/js/slide.js',
            [],
            '1.0',
            true
        );

    }

}
add_action('wp_enqueue_scripts','weselny_particles_scripts');


/**
 * 🔥 AKTYWNY MODUŁ
 */
function weselny_get_active_module(){

    if(!empty($_GET['custom_id'])){
        return 'custom';
    }

    $modules = ['stoly','galeria','menu','harmonogram','quiz','zadania','ksiega'];

    foreach($modules as $m){
        if(!empty($_GET[$m])){
            return $m;
        }
    }

    return false;
}