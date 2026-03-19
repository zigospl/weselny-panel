<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function weselny_harmonogram_guest($content){

if(get_post_type()!=='wesela' || !is_singular('wesela')) return $content;

$post_id = get_the_ID();

$user_id = get_post_meta($post_id,'user_id',true);
if(!$user_id) return $content;

$enabled = get_user_meta($user_id,'weselny_modul_harmonogram',true);
if(!$enabled) return $content;


/* =========================
   WIDOK
========================= */

if(isset($_GET['harmonogram'])){

$data = get_post_meta($post_id,'weselny_harmonogram',true);

$html = '<p><a href="'.get_permalink().'">← Powrót</a></p>';
$html .= '<h2>Harmonogram</h2>';

if($data){

foreach($data as $row){

$html .= '<div style="margin-bottom:20px;">';

$html .= '<strong>'.$row['time'].'</strong><br>';
$html .= '<h3>'.$row['title'].'</h3>';
$html .= '<p>'.$row['description'].'</p>';

$html .= '</div>';

}

}

return $html;
}


/* =========================
   BLOKADA
========================= */

if(isset($_GET['stoly']) || isset($_GET['galeria']) || isset($_GET['menu'])){
    return $content;
}


/* =========================
   KAFEL
========================= */

$url = add_query_arg('harmonogram','1',get_permalink());

$html = '<div style="border:1px solid #ccc;padding:20px;display:inline-block;margin:10px;">';
$html .= '<a href="'.$url.'">Harmonogram</a>';
$html .= '</div>';

return $content.$html;

}

add_filter('the_content','weselny_harmonogram_guest');