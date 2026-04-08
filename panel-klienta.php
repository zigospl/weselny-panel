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

    return wc_customer_bought_product('', get_current_user_id(), $product_id);

}


/**
 * Menu WooCommerce
 */

function wp_weselny_panel_menu_item( $items ) {

    if ( wp_weselny_panel_user_has_product(19) ) {

        $new_items = array();

        foreach ( $items as $key => $label ) {

            $new_items[$key] = $label;

            if ( $key === 'dashboard' ) {
                $new_items['panel-wesela'] = 'Panel wesela';
            }

        }

        return $new_items;

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

        echo '<p style="padding-top:20px;"><a href="'.esc_url($url).'" target="_blank">'.$url.'</a></p>';

        echo '<hr>';

        echo '<h3>Funkcje panelu</h3>';

        echo '<div class="weselny-tile">';
        echo '<a href="'.wc_get_account_endpoint_url('funkcje-panelu').'">Funkcje panelu</a>';
        echo '</div>';

        do_action('weselny_panel_tiles');

    }

}

add_action('woocommerce_account_panel-wesela_endpoint','wp_weselny_panel_content');


/**
 * Strona Funkcje panelu
 */

function wp_weselny_panel_features_page() {

echo '<h2>Funkcje panelu</h2>';

/* KOMUNIKAT */
echo '<div id="weselny-save-msg" style="
display:none;
background:#d4edda;
color:#155724;
padding:10px 15px;
margin-bottom:15px;
border-radius:6px;
font-weight:500;
">
Zmiany zapisane
</div>';

echo '<p><a href="'.wc_get_account_endpoint_url('panel-wesela').'">← Powrót do panelu</a></p>';

echo '<form method="post">';

do_action('weselny_panel_features');

echo '<br><button name="weselny_features_save">Zapisz</button>';

echo '</form>';

/* SKRYPT */
if(isset($_POST['weselny_features_save'])){
    echo '<script>
    document.addEventListener("DOMContentLoaded", function(){
        const msg = document.getElementById("weselny-save-msg");
        if(msg){
            msg.style.display = "block";
            msg.style.opacity = "0";
            msg.style.transition = "opacity 0.3s";

            setTimeout(()=>{ msg.style.opacity = "1"; }, 50);

            setTimeout(()=>{
                msg.style.opacity = "0";
                setTimeout(()=>{ msg.style.display = "none"; }, 300);
            }, 2000);
        }
    });
    </script>';
}

}

add_action('woocommerce_account_funkcje-panelu_endpoint','wp_weselny_panel_features_page');