<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function weselny_custom_guest($content){

if(get_post_type()!=='wesela' || !is_singular('wesela')) return $content;

$post_id = get_the_ID();

$data = get_post_meta($post_id,'weselny_custom_modules',true);

if(!$data) return $content;


/* =========================
   WIDOK MODUŁU
========================= */

if(isset($_GET['custom_id'])){

$i = intval($_GET['custom_id']);

if(!isset($data[$i])) return $content;

$mod = $data[$i];

$html = '<p><a href="'.get_permalink().'">← Powrót</a></p>';

if(!empty($mod['blocks'])){
foreach($mod['blocks'] as $b){

$type = $b['type'] ?? '';
$value = $b['value'] ?? '';

if($type=='h1'){
$html .= '<h1>'.esc_html($value).'</h1>';
}
elseif($type=='h2'){
$html .= '<h3>'.esc_html($value).'</h3>';
}
elseif($type=='text'){
$html .= '<div>'.$value.'</div>';
}
elseif($type=='img' && !empty($value)){
$html .= '<img src="'.esc_url($value).'" style="width:100%;max-width:400px;display:block;margin-bottom:15px;">';
}

}
}

/* 🔥 KLUCZOWE — blokujemy inne moduły */
return $html;

}


/* =========================
   BLOKADA GLOBALNA (FIX)
========================= */

/* jeśli jesteśmy w custom — nic więcej nie renderuj */
if(isset($_GET['custom_id'])){
    return $content;
}

$active = weselny_get_active_module();

if($active && $active !== 'custom'){
    return $content;
}


/* =========================
   KAFELKI
========================= */

$html = '';

foreach($data as $i=>$mod){

$title = !empty($mod['title']) ? $mod['title'] : 'Kafel '.($i+1);

$url = add_query_arg('custom_id',$i,get_permalink());

$html .= '<div class="weselny-tile">';
$html .= '<a href="'.$url.'">'.esc_html($title).'</a>';
$html .= '</div>';

}

return $content.$html;

}

add_filter('the_content','weselny_custom_guest');