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

        echo '<h2>Witaj w Panelu weselnym!</h2>';

        echo '<p>Udostępnij gościom ten kod QR:</p>';

        echo '<img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data='.esc_url($url).'">';

        echo '<p><a href="'.esc_url($url).'" target="_blank">'.$url.'</a></p>';

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
   MODUŁY (DEFAULT)
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


/* =========================
   LABELKI
========================= */

$custom_labels = get_post_meta($post_id,'weselny_module_labels',true);
if(!is_array($custom_labels)) $custom_labels = [];


/* =========================
   AKTYWNE MODUŁY
========================= */

$modules = [];

foreach($base_modules as $key=>$mod){

    if(get_user_meta($user_id,$mod['meta'],true)){

        $modules[$key] = !empty($custom_labels[$key]) 
            ? $custom_labels[$key] 
            : $mod['label'];
    }
}


/* =========================
   CUSTOM SEKCJE
========================= */

$custom = get_post_meta($post_id,'weselny_custom_modules',true);

if(!empty($custom) && is_array($custom)){
    foreach($custom as $i=>$c){

        $default = !empty($c['title']) ? $c['title'] : 'Sekcja '.($i+1);
        $key = 'custom_'.$i;

        $modules[$key] = !empty($custom_labels[$key])
            ? $custom_labels[$key]
            : '🧩 '.$default;
    }
}


/* =========================
   KOLEJNOŚĆ
========================= */

$order = get_post_meta($post_id,'weselny_module_order',true);

if(!is_array($order)){
    $order = array_keys($modules);
}

$order = array_values(array_intersect($order, array_keys($modules)));

foreach(array_keys($modules) as $key){
    if(!in_array($key,$order)){
        $order[] = $key;
    }
}


/* =========================
   ZAPIS + REDIRECT
========================= */

if(isset($_POST['module_order'])){

    $clean_order = array_map('sanitize_text_field', $_POST['module_order']);
    update_post_meta($post_id,'weselny_module_order',$clean_order);

    $labels = [];

    if(isset($_POST['module_label'])){
        foreach($_POST['module_label'] as $key=>$val){

            $val = trim($val);

            if($val !== ''){
                $labels[$key] = sanitize_text_field($val);
            }
        }
    }

    update_post_meta($post_id,'weselny_module_labels',$labels);

    /* 🔥 PRG FIX */
    wp_redirect( add_query_arg('saved','1', wc_get_account_endpoint_url('funkcje-panelu')) );
    exit;
}


/* =========================
   UI
========================= */

echo '<h2>Kolejność modułów</h2>';

echo '<div id="weselny-save-msg" style="display:none;">Zapisano</div>';

echo '<p><a href="'.wc_get_account_endpoint_url('panel-wesela').'">← Powrót</a></p>';

echo '<form method="post">';

echo '<div id="sortable">';

foreach($order as $key){

    /* 🔥 DEFAULT LABEL */
    if(isset($base_modules[$key])){
        $default_label = $base_modules[$key]['label'];
    } elseif(strpos($key,'custom_') === 0){
        $i = intval(str_replace('custom_','',$key));
        $default_label = !empty($custom[$i]['title']) ? $custom[$i]['title'] : 'Sekcja '.($i+1);
    } else {
        $default_label = '';
    }

    $value = $custom_labels[$key] ?? '';

    echo '<div class="drag-item" draggable="true" data-key="'.$key.'">

    <span class="drag-handle">☰</span>

    <input 
        type="text" 
        name="module_label['.$key.']" 
        value="'.esc_attr($value).'" 
        placeholder="'.esc_attr($default_label).'" 
        class="module-label-input"
    >

    <input type="hidden" name="module_order[]" value="'.$key.'">

    </div>';
}

echo '</div>';

echo '<br><button>Zapisz</button>';
echo '</form>';

echo '<hr>';

echo '<form method="post">';
do_action('weselny_panel_features');
echo '<br><button name="weselny_features_save">Zapisz</button>';
echo '</form>';


/* =========================
   JS DRAG
========================= */

echo '
<script>

let dragged = null;

document.querySelectorAll(".drag-item").forEach(item => {

item.addEventListener("dragstart", () => {
dragged = item;
item.classList.add("dragging");
});

item.addEventListener("dragend", () => {
item.classList.remove("dragging");
});

});

const container = document.getElementById("sortable");

container.addEventListener("dragover", e => {
e.preventDefault();

const afterElement = getDragAfterElement(container, e.clientY);
const dragging = document.querySelector(".dragging");

if(!afterElement){
container.appendChild(dragging);
}else{
container.insertBefore(dragging, afterElement);
}

});

function getDragAfterElement(container, y){

const elements = [...container.querySelectorAll(".drag-item:not(.dragging)")];

return elements.reduce((closest, child) => {

const box = child.getBoundingClientRect();
const offset = y - box.top - box.height / 2;

if(offset < 0 && offset > closest.offset){
return { offset: offset, element: child };
}else{
return closest;
}

}, { offset: Number.NEGATIVE_INFINITY }).element;

}

</script>
';


/* =========================
   KOMUNIKAT
========================= */

if(isset($_GET['saved'])){
echo '<script>
document.addEventListener("DOMContentLoaded",()=>{
let msg=document.getElementById("weselny-save-msg");
msg.style.display="block";
setTimeout(()=>msg.style.display="none",2000);
});
</script>';
}

}

add_action('woocommerce_account_funkcje-panelu_endpoint','wp_weselny_panel_features_page');