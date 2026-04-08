<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function weselny_menu_guest($content){

if(get_post_type()!=='wesela' || !is_singular('wesela')) return $content;

$post_id = get_the_ID();

$user_id = get_post_meta($post_id,'user_id',true);
if(!$user_id) return $content;

$enabled = get_user_meta($user_id,'weselny_modul_menu',true);
if(!$enabled) return $content;


/* =========================
   1. WIDOK MENU
========================= */

if(isset($_GET['menu'])){

$data = get_post_meta($post_id,'weselny_menu',true);

$html = '<p><a href="'.get_permalink().'">← Powrót</a></p>';
$html .= '<h2>Menu weselne</h2>';

if($data){

foreach($data as $section){

$html .= '<h3>'.$section['title'].'</h3>';
$html .= '<div>'.$section['content'].'</div><br>';

}

}

return $html;
}


/* =========================
   2. BLOKADA (inne moduły)
========================= */

$active = weselny_get_active_module();

if($active && $active !== 'quiz'){
    return $content;
}


/* =========================
   3. KAFEL
========================= */

$url = add_query_arg('menu','1',get_permalink());

$html = '<div class="weselny-tile">';
$html .= '<a href="'.$url.'">Menu</a>';
$html .= '</div>';

return $content.$html;

}

add_filter('the_content','weselny_menu_guest');