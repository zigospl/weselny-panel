<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Endpointy
 */

function wp_weselny_panel_add_endpoints() {

    add_rewrite_endpoint( 'panel-wesela', EP_ROOT | EP_PAGES );
    add_rewrite_endpoint( 'funkcje-panelu', EP_ROOT | EP_PAGES );

}

add_action( 'init', 'wp_weselny_panel_add_endpoints' );


/**
 * Sprawdzenie produktu
 */

function wp_weselny_panel_user_has_product( $product_id ) {

    if ( ! is_user_logged_in() ) {
        return false;
    }

    return wc_customer_bought_product('',get_current_user_id(),$product_id);

}


/**
 * Menu
 */

function wp_weselny_panel_menu_item( $items ) {

    if ( wp_weselny_panel_user_has_product(19) ) {

        $items['panel-wesela'] = 'Panel wesela';
        $items['funkcje-panelu'] = 'Funkcje panelu';

    }

    return $items;

}

add_filter('woocommerce_account_menu_items','wp_weselny_panel_menu_item');


/**
 * Panel wesela
 */

function wp_weselny_panel_content() {

    $user_id = get_current_user_id();

    $wedding = get_posts(array(
        'post_type'=>'wesela',
        'meta_key'=>'user_id',
        'meta_value'=>$user_id,
        'posts_per_page'=>1
    ));

    if($wedding){

        $post = $wedding[0];
        $url = get_permalink($post->ID);

        echo '<h2>Witaj w Panelu weselnym!</h2>';

        echo '<p>Udostępnij gościom ten kod QR:</p>';

        echo '<img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data='.esc_url($url).'">';

        echo '<hr>';

        echo '<h3>Funkcje panelu</h3>';

        do_action('weselny_panel_tiles');

    }

}

add_action('woocommerce_account_panel-wesela_endpoint','wp_weselny_panel_content');


/**
 * Strona funkcji panelu
 */

function wp_weselny_panel_features_page() {

    echo '<h2>Funkcje panelu</h2>';

    echo '<form method="post">';

    do_action('weselny_panel_features');

    echo '</form>';

}

add_action('woocommerce_account_funkcje-panelu_endpoint','wp_weselny_panel_features_page');