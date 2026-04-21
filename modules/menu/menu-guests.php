<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =========================
   RENDER MODUŁU
========================= */

function weselny_menu_render($post_id){

$user_id = get_post_meta($post_id,'user_id',true);
if(!$user_id) return;

$enabled = get_user_meta($user_id,'weselny_modul_menu',true);
if(!$enabled) return;


/* =========================
   WIDOK MENU
========================= */

if(isset($_GET['menu'])){

$data = get_post_meta($post_id,'weselny_menu',true);

echo '<p><a href="'.get_permalink().'" class="weselny-back">← Powrót</a></p>';
echo '<h2>Menu weselne</h2>';

if(!empty($data)){

foreach($data as $section){

$title = $section['title'] ?? '';
$content = $section['content'] ?? '';

echo '<h3>'.esc_html($title).'</h3>';
echo '<div>'.$content.'</div><br>'; // WYSIWYG

}

}else{
echo '<p>Brak menu</p>';
}

return;
}


/* =========================
   BLOKADA
========================= */

$active = weselny_get_active_module();

if($active && $active !== 'menu'){
return;
}


/* =========================
   KAFEL
========================= */

$url = add_query_arg('menu','1',get_permalink($post_id));

echo '<div class="weselny-tile">';
echo '<a href="'.esc_url($url).'">Menu</a>';
echo '</div>';

}

/* 🔥 NOWY SYSTEM */
add_action('weselny_render_module_menu','weselny_menu_render');