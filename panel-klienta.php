<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Dodanie endpointu do WooCommerce
 */
function wp_weselny_panel_add_endpoint() {
    add_rewrite_endpoint( 'panel-wesela', EP_ROOT | EP_PAGES );
}
add_action( 'init', 'wp_weselny_panel_add_endpoint' );


/**
 * Sprawdzenie czy użytkownik kupił produkt
 */
function wp_weselny_panel_user_has_product( $product_id ) {

    if ( ! is_user_logged_in() ) {
        return false;
    }

    $user_id = get_current_user_id();

    return wc_customer_bought_product( '', $user_id, $product_id );
}


/**
 * Dodanie zakładki w menu Moje Konto
 */
function wp_weselny_panel_menu_item( $items ) {

    if ( wp_weselny_panel_user_has_product( 19 ) ) {
        $items['panel-wesela'] = 'Panel wesela';
    }

    return $items;
}
add_filter( 'woocommerce_account_menu_items', 'wp_weselny_panel_menu_item' );


/**
 * Zawartość panelu weselnego
 */
function wp_weselny_panel_content() {

    if ( ! wp_weselny_panel_user_has_product( 19 ) ) {
        echo '<p>Nie masz dostępu do panelu wesela.</p>';
        return;
    }

    $user_id = get_current_user_id();

    $wedding = get_posts(array(
        'post_type' => 'wesela',
        'meta_key' => 'user_id',
        'meta_value' => $user_id,
        'posts_per_page' => 1
    ));

    if ( $wedding ) {

        $post = $wedding[0];

        echo apply_filters( 'the_content', $post->post_content );

    }

}
add_action( 'woocommerce_account_panel-wesela_endpoint', 'wp_weselny_panel_content' );