<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function weselny_stoly_guest_module($content){

if(get_post_type()!=='wesela' || !is_singular('wesela')) return $content;

$post_id = get_the_ID();

/* właściciel wesela */
$user_id = get_post_meta($post_id,'user_id',true);
if(!$user_id) return $content;

/* czy moduł włączony */
$enabled = get_user_meta($user_id,'weselny_modul_stoly',true);
if(!$enabled) return $content;

/* dane */
$data = get_post_meta($post_id,'weselny_stoly',true);
if(!$data) return $content;


/* =========================
   1. WIDOK STOŁÓW
========================= */

if(isset($_GET['stoly'])){

$back = get_permalink();

$html = '<p><a href="'.$back.'">← Powrót do panelu</a></p>';
$html .= '<h2>Rozstaw stołów</h2>';

foreach($data as $table){

$html .= '<h3>'.$table['title'].'</h3>';

$hasSeat = false;

foreach($table['guests'] as $g){
if(!empty($g['seat'])) $hasSeat = true;
}

$html .= '<table border="1" cellpadding="6">';

foreach($table['guests'] as $g){

$html .= '<tr>';

$html .= '<td>'.$g['name'].'</td>';

if($hasSeat){
$html .= '<td>'.$g['seat'].'</td>';
}

$html .= '</tr>';

}

$html .= '</table><br>';

}

return $html;
}


/* =========================
   2. BLOKADA (inne moduły)
========================= */

if(isset($_GET['galeria']) || isset($_GET['menu']) || isset($_GET['harmonogram'])){
    return $content;
}


/* =========================
   3. KAFEL
========================= */

$url = add_query_arg('stoly','1',get_permalink());

$html = '<div style="border:1px solid #ccc;padding:20px;display:inline-block;margin:10px;">';
$html .= '<a href="'.$url.'">Rozstaw stołów</a>';
$html .= '</div>';

return $content.$html;

}

add_filter('the_content','weselny_stoly_guest_module');