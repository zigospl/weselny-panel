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
    if ( ! is_user_logged_in() ) return false;
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
 * PANEL WESELA
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

        $qr = 'https://api.qrserver.com/v1/create-qr-code/?size=600x600&data='.urlencode($url);

        echo '<h2 style="text-align:center;">Witaj w Panelu weselnym!</h2>';
        echo '<p style="text-align:center;">Udostępnij gościom ten kod QR:</p>';

        echo '<div style="text-align:center;margin-bottom:20px;">';

        echo '<img id="weselny-qr" crossorigin="anonymous" src="'.$qr.'" style="max-width:220px;"><br><br>';

        echo '<button type="button" id="download-qr">Pobierz grafikę QR</button>';

        echo '</div>';

        echo '<hr>';

        echo '<h3>Funkcje panelu</h3>';

        echo '<div class="weselny-tile"><a href="'.wc_get_account_endpoint_url('funkcje-panelu').'">Funkcje panelu</a></div>';
        echo '<div class="weselny-tile"><a href="'.wc_get_account_endpoint_url('panel-wesela').'?wyglad=1">Wygląd panelu</a></div>';
        echo '<div class="weselny-tile"><a href="'.wc_get_account_endpoint_url('panel-wesela').'?custom=1">Własne sekcje</a></div>';

        remove_action('weselny_panel_tiles','weselny_wyglad_tile');
        remove_action('weselny_panel_tiles','weselny_custom_tile');

        do_action('weselny_panel_tiles');

        echo '<script>
        document.getElementById("download-qr").addEventListener("click", function(){

            const img = document.getElementById("weselny-qr");

            const canvas = document.createElement("canvas");
            const ctx = canvas.getContext("2d");

            canvas.width = img.naturalWidth;
            canvas.height = img.naturalHeight;

            ctx.drawImage(img, 0, 0);

            canvas.toBlob(function(blob){
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = "qr-wesele.png";
                link.click();
            }, "image/png");

        });
        </script>';
    }
}
add_action('woocommerce_account_panel-wesela_endpoint','wp_weselny_panel_content');


/**
 * STRONA FUNKCJI
 */
function wp_weselny_panel_features_page() {

$user_id = get_current_user_id();

$wedding = get_posts([
'post_type'=>'wesela',
'meta_key'=>'user_id',
'meta_value'=>$user_id,
'posts_per_page'=>1
]);

$post_id = $wedding ? $wedding[0]->ID : 0;


/* =========================
   MODUŁY
========================= */

$base_modules = [
'stoly' => ['label'=>'Rozstaw stołów','meta'=>'weselny_modul_stoly'],
'harmonogram' => ['label'=>'Harmonogram','meta'=>'weselny_modul_harmonogram'],
'menu' => ['label'=>'Menu','meta'=>'weselny_modul_menu'],
'ksiega' => ['label'=>'Księga gości','meta'=>'weselny_modul_ksiega'],
'quiz' => ['label'=>'Quiz','meta'=>'weselny_modul_quiz'],
'zadania' => ['label'=>'Zadania','meta'=>'weselny_modul_zadania'],
'zdjecia' => ['label'=>'Zdjęcia','meta'=>'weselny_modul_zdjecia'],
];

$custom_labels = get_post_meta($post_id,'weselny_module_labels',true);
if(!is_array($custom_labels)) $custom_labels = [];

$modules = [];

/* 🔥 WSZYSTKIE moduły (nawet wyłączone!) */
foreach($base_modules as $key=>$mod){
    $modules[$key] = $custom_labels[$key] ?? $mod['label'];
}

$custom = get_post_meta($post_id,'weselny_custom_modules',true);
if(!empty($custom)){
    foreach($custom as $i=>$c){
        $modules['custom_'.$i] = $custom_labels['custom_'.$i] ?? ($c['title'] ?? 'Sekcja');
    }
}


/* =========================
   KOLEJNOŚĆ
========================= */

$order = get_post_meta($post_id,'weselny_module_order',true);
if(!is_array($order)) $order = array_keys($modules);

$order = array_values(array_intersect($order, array_keys($modules)));
foreach(array_keys($modules) as $k){
    if(!in_array($k,$order)) $order[] = $k;
}


/* =========================
   ZAPIS
========================= */

if(isset($_POST['module_order'])){

    update_post_meta($post_id,'weselny_module_order', $_POST['module_order']);

    $labels = [];
    if(isset($_POST['module_label'])){
        foreach($_POST['module_label'] as $k=>$v){
            if(trim($v) !== ''){
                $labels[$k] = sanitize_text_field($v);
            }
        }
    }

    update_post_meta($post_id,'weselny_module_labels',$labels);

    wp_redirect( wc_get_account_endpoint_url('funkcje-panelu') );
    exit;
}


/* =========================
   UI – SORTOWANIE
========================= */

echo '<h2>Kolejność modułów</h2>';
echo '<p><a href="'.wc_get_account_endpoint_url('panel-wesela').'">← Powrót</a></p>';

echo '<form method="post" id="modules-form">';
echo '<div id="sortable">';

foreach($order as $key){

    echo '<div class="drag-item" draggable="true">
        ☰ 
        <input type="text" name="module_label['.$key.']" value="'.esc_attr($custom_labels[$key] ?? '').'" placeholder="'.esc_attr($modules[$key]).'">
        <input type="hidden" class="order-input" value="'.$key.'">
    </div>';
}

echo '</div>';
echo '<br><button>Zapisz kolejność</button>';
echo '</form>';


/* =========================
   🔥 CHECKBOXY WRACAJĄ
========================= */

echo '<hr>';

echo '<form method="post">';
do_action('weselny_panel_features');
echo '<br><button name="weselny_features_save">Zapisz funkcje</button>';
echo '</form>';


/* =========================
   JS DRAG
========================= */

echo '<script>

let dragged;

document.querySelectorAll(".drag-item").forEach(el=>{

    el.addEventListener("dragstart", ()=>{
        dragged = el;
        el.style.opacity = "0.5";
    });

    el.addEventListener("dragend", ()=>{
        el.style.opacity = "1";
    });

});

document.getElementById("sortable").addEventListener("dragover", e=>{
    e.preventDefault();

    const items = [...document.querySelectorAll(".drag-item:not(.dragging)")];

    let closest = null;
    let offset = Number.NEGATIVE_INFINITY;

    items.forEach(el=>{
        const box = el.getBoundingClientRect();
        const diff = e.clientY - box.top - box.height/2;

        if(diff < 0 && diff > offset){
            offset = diff;
            closest = el;
        }
    });

    if(!closest){
        e.currentTarget.appendChild(dragged);
    } else {
        e.currentTarget.insertBefore(dragged, closest);
    }
});


document.getElementById("modules-form").addEventListener("submit", function(){

    document.querySelectorAll("input[name=\'module_order[]\']").forEach(el=>el.remove());

    document.querySelectorAll(".drag-item").forEach(el=>{
        let key = el.querySelector(".order-input").value;

        let input = document.createElement("input");
        input.type = "hidden";
        input.name = "module_order[]";
        input.value = key;

        this.appendChild(input);
    });

});

</script>';

}
add_action('woocommerce_account_funkcje-panelu_endpoint','wp_weselny_panel_features_page');