<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/* =========================
   RENDER MODUŁU
========================= */

function weselny_ksiega_render($post_id){

$user_id = get_post_meta($post_id,'user_id',true);
if(!$user_id) return;

$enabled = get_user_meta($user_id,'weselny_modul_ksiega',true);
if(!$enabled) return;


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

echo '<p><a href="'.get_permalink().'" class="weselny-back">← Powrót</a></p>';

echo '<h2>Księga gości</h2>';

/* FORM */

echo '
<form method="post" enctype="multipart/form-data">

<input type="text" name="name" placeholder="Twoje imię" required><br><br>

<textarea name="content" placeholder="Napisz coś..." required></textarea><br><br>

<input type="file" name="img" accept="image/*"><br><br>

<button name="ksiega_add">Dodaj wpis</button>

</form>

<hr>
';

/* WPISY */

foreach(array_reverse($data) as $entry){

$name = $entry['name'] ?? '';
$content = $entry['content'] ?? '';
$img = $entry['img'] ?? '';

echo '<div style="border:1px solid #ccc;padding:15px;margin-bottom:15px;">';

echo '<strong>'.esc_html($name).'</strong><br><br>';

echo $content.'<br>'; // WYSIWYG

if(!empty($img)){
echo '<a href="'.esc_url($img).'"><img src="'.esc_url($img).'" style="width:120px;margin-top:10px;"></a>';
}

echo '</div>';

}

return;
}


/* =========================
   BLOKADA
========================= */

$active = weselny_get_active_module();

if($active && $active !== 'ksiega'){
return;
}


/* =========================
   KAFEL
========================= */

$url = add_query_arg('ksiega','1',get_permalink($post_id));

echo '<div class="weselny-tile">';
echo '<a href="'.esc_url($url).'">Księga gości</a>';
echo '</div>';

}

/* 🔥 NOWY SYSTEM */
add_action('weselny_render_module_ksiega','weselny_ksiega_render');