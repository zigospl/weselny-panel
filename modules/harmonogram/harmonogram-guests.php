<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =========================
   RENDER MODUŁU
========================= */

function weselny_harmonogram_render($post_id){

$user_id = get_post_meta($post_id,'user_id',true);
if(!$user_id) return;

$enabled = get_user_meta($user_id,'weselny_modul_harmonogram',true);
if(!$enabled) return;


/* =========================
   WIDOK
========================= */

if(isset($_GET['harmonogram'])){

$data = get_post_meta($post_id,'weselny_harmonogram',true);

echo '<p><a href="'.get_permalink().'" class="weselny-back">← Powrót</a></p>';
echo '<h2>Harmonogram</h2>';

if(!empty($data)){

foreach($data as $row){

$time = $row['time'] ?? '';
$title = $row['title'] ?? '';
$desc = $row['description'] ?? '';

echo '<div style="margin-bottom:20px;">';

echo '<strong>'.esc_html($time).'</strong><br>';
echo '<h3>'.esc_html($title).'</h3>';
echo '<p>'.$desc.'</p>'; // WYSIWYG

echo '</div>';

}

}else{
echo '<p>Brak harmonogramu</p>';
}

return;
}


/* =========================
   BLOKADA
========================= */

$active = weselny_get_active_module();

if($active && $active !== 'harmonogram'){
return;
}


/* =========================
   KAFEL
========================= */

$url = add_query_arg('harmonogram','1',get_permalink($post_id));

echo '<div class="weselny-tile">';
echo '<a href="'.esc_url($url).'">Harmonogram</a>';
echo '</div>';

}

/* 🔥 NOWY SYSTEM */
add_action('weselny_render_module_harmonogram','weselny_harmonogram_render');