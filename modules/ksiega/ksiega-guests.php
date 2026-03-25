<?php

if ( ! defined( 'ABSPATH' ) ) exit;

function weselny_ksiega_guest($content){

if(get_post_type()!=='wesela' || !is_singular('wesela')) return $content;

$post_id = get_the_ID();

$user_id = get_post_meta($post_id,'user_id',true);
if(!$user_id) return $content;

$enabled = get_user_meta($user_id,'weselny_modul_ksiega',true);
if(!$enabled) return $content;


/* =========================
   DODAWANIE
========================= */

if(isset($_POST['ksiega_add'])){

require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');

$data = get_post_meta($post_id,'weselny_ksiega',true);
if(!$data) $data = [];

$img_url = '';

if(!empty($_FILES['img']['tmp_name'])){

$upload = wp_handle_upload($_FILES['img'],['test_form'=>false]);

if(!isset($upload['error'])){
$img_url = $upload['url'];
}

}

$data[] = [
'name'=>sanitize_text_field($_POST['name']),
'content'=>wp_kses_post($_POST['content']),
'img'=>$img_url
];

update_post_meta($post_id,'weselny_ksiega',$data);

}


/* =========================
   WIDOK
========================= */

if(isset($_GET['ksiega'])){

$data = get_post_meta($post_id,'weselny_ksiega',true);
if(!$data) $data = [];

$html = '<p><a href="'.get_permalink().'">← Powrót</a></p>';

$html .= '<h2>Księga gości</h2>';

/* FORM */

$html .= '
<form method="post" enctype="multipart/form-data">

<input type="text" name="name" placeholder="Twoje imię" required><br><br>

<textarea name="content" placeholder="Napisz coś..." required></textarea><br><br>

<input type="file" name="img" accept="image/*"><br><br>

<button name="ksiega_add">Dodaj wpis</button>

</form>

<hr>
';

/* POSTY */

foreach(array_reverse($data) as $entry){

$html .= '<div style="border:1px solid #ccc;padding:15px;margin-bottom:15px;">';

$html .= '<strong>'.$entry['name'].'</strong><br><br>';

$html .= $entry['content'].'<br>';

if(!empty($entry['img'])){
$html .= '<a href="'.$entry['img'].'"><img src="'.$entry['img'].'" style="width:120px;margin-top:10px;"></a>';
}

$html .= '</div>';

}

return $html;

}


/* =========================
   BLOKADA
========================= */

$active = weselny_get_active_module();

if($active && $active !== 'ksiega'){
return $content;
}


/* =========================
   KAFEL
========================= */

$url = add_query_arg('ksiega','1',get_permalink());

$html = '<div style="border:1px solid #ccc;padding:20px;display:inline-block;margin:10px;">';
$html .= '<a href="'.$url.'">Księga gości</a>';
$html .= '</div>';

return $content.$html;

}

add_filter('the_content','weselny_ksiega_guest');