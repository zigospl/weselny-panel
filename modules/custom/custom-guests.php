<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* =========================
   RENDER MODUŁU
========================= */

function weselny_custom_render($post_id){

$data = get_post_meta($post_id,'weselny_custom_modules',true);
if(!$data) return;


/* =========================
   WIDOK POJEDYNCZEJ SEKCJI
========================= */

if(isset($_GET['custom_id'])){

$i = intval($_GET['custom_id']);

if(!isset($data[$i])) return;

$mod = $data[$i];

echo '<p><a href="'.get_permalink().'" class="weselny-back">← Powrót</a></p>';

if(!empty($mod['blocks'])){

foreach($mod['blocks'] as $b){

$type = $b['type'] ?? '';
$value = $b['value'] ?? '';

if($type=='h1'){
echo '<h1>'.esc_html($value).'</h1>';
}
elseif($type=='h2'){
echo '<h3>'.esc_html($value).'</h3>';
}
elseif($type=='text'){
echo '<div>'.$value.'</div>'; // WYSIWYG
}
elseif($type=='img' && !empty($value)){
echo '<img src="'.esc_url($value).'" style="width:100%;max-width:400px;display:block;margin-bottom:15px;">';
}

}

}

return;
}


/* =========================
   🔥 KLUCZOWA BLOKADA GLOBALNA
========================= */

/* jeśli JAKIKOLWIEK moduł jest otwarty → nie pokazuj custom */
$active = weselny_get_active_module();

if($active && $active !== 'custom'){
return;
}


/* dodatkowe zabezpieczenie:
   jeśli jesteśmy w custom view → nie pokazuj kafelków innych */
if(isset($_GET['custom_id'])){
return;
}


/* =========================
   KAFELKI
========================= */

foreach($data as $i=>$mod){

$title = !empty($mod['title']) ? $mod['title'] : 'Sekcja '.($i+1);

$url = add_query_arg('custom_id',$i,get_permalink($post_id));

echo '<div class="weselny-tile">';
echo '<a href="'.esc_url($url).'">'.esc_html($title).'</a>';
echo '</div>';

}

}

/* 🔥 NOWY SYSTEM */
add_action('weselny_render_module_custom','weselny_custom_render');