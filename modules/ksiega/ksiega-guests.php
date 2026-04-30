<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/* =========================
   DEVICE ID
========================= */

function weselny_get_device_id(){

if(isset($_COOKIE['weselny_device'])){
return $_COOKIE['weselny_device'];
}

$id = wp_generate_uuid4();
setcookie('weselny_device',$id,time()+3600*24*365,'/');

return $id;
}


/* =========================
   RENDER
========================= */

function weselny_ksiega_render($post_id){

$user_id = get_post_meta($post_id,'user_id',true);
if(!$user_id) return;

$enabled = get_user_meta($user_id,'weselny_modul_ksiega',true);
if(!$enabled) return;

$device_id = weselny_get_device_id();


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

$name = sanitize_text_field($_POST['name'] ?? '');
$content = wp_kses_post($_POST['content'] ?? '');

if($name || $content || $img_url){

$data[] = [
'name'=>$name,
'content'=>$content,
'img'=>$img_url,
'device'=>$device_id
];

update_post_meta($post_id,'weselny_ksiega',$data);

}

wp_redirect(add_query_arg('ksiega','1',get_permalink($post_id)));
exit;

}


/* =========================
   USUWANIE
========================= */

if(isset($_POST['ksiega_delete'])){

$data = get_post_meta($post_id,'weselny_ksiega',true);
if(!$data) $data = [];

$index = intval($_POST['ksiega_delete']);

if(isset($data[$index]) && $data[$index]['device'] === $device_id){

unset($data[$index]);
$data = array_values($data);

update_post_meta($post_id,'weselny_ksiega',$data);

}

wp_redirect(add_query_arg('ksiega','1',get_permalink($post_id)));
exit;

}


/* =========================
   WIDOK
========================= */

if(isset($_GET['ksiega'])){

$data = get_post_meta($post_id,'weselny_ksiega',true);
if(!$data) $data = [];

echo '
<style>
.ks-container{
max-width:600px;
margin:0 auto;
font-family:Arial;
}

/* FORM */

.ks-form{
background:#fff;
padding:15px;
border-radius:12px;
box-shadow:0 4px 12px rgba(0,0,0,0.08);
margin-bottom:20px;
}

.ks-form input,
.ks-form textarea{
width:100%;
padding:10px;
border:1px solid #ddd;
border-radius:8px;
margin-bottom:10px;
}

.ks-form button{
width:100%;
background:#1a3d1a;
color:#fff;
padding:12px;
border:none;
border-radius:8px;
cursor:pointer;
}

/* POST */

.ks-post{
background:#fff;
border-radius:12px;
box-shadow:0 4px 12px rgba(0,0,0,0.08);
margin-bottom:15px;
overflow:hidden;
}

.ks-header{
display:flex;
align-items:center;
gap:10px;
padding:12px;
}

.ks-avatar{
width:40px;
height:40px;
border-radius:50%;
background:#ddd;
display:flex;
align-items:center;
justify-content:center;
font-weight:bold;
}

.ks-name{
font-weight:600;
}

.ks-content{
padding:0 12px 12px;
}

.ks-img img{
width:100%;
display:block;
}

.ks-actions{
padding:10px 12px;
border-top:1px solid #eee;
}

.ks-delete{
background:#e74c3c;
color:#fff;
border:none;
padding:6px 10px;
border-radius:6px;
cursor:pointer;
font-size:12px;
}
</style>
';

echo '<div class="ks-container">';

echo '<p><a href="'.get_permalink().'" class="weselny-back">← Powrót</a></p>';
echo '<h2>Księga gości</h2>';

/* FORM */

echo '
<form method="post" enctype="multipart/form-data" class="ks-form">

<input type="text" name="name" placeholder="Twoje imię" required>

<textarea name="content" placeholder="Napisz coś..." required></textarea>

<input type="file" name="img" accept="image/*">

<button name="ksiega_add">Dodaj wpis</button>

</form>
';


/* POSTS */

foreach(array_reverse($data,true) as $index=>$entry){

$name = esc_html($entry['name'] ?? '');
$content = $entry['content'] ?? '';
$img = $entry['img'] ?? '';
$owner = $entry['device'] ?? '';

$avatar = strtoupper(substr($name,0,1));

echo '<div class="ks-post">';

/* HEADER */

echo '
<div class="ks-header">
<div class="ks-avatar">'.$avatar.'</div>
<div class="ks-name">'.$name.'</div>
</div>
';

/* CONTENT */

echo '<div class="ks-content">'.$content.'</div>';

/* IMG */

if(!empty($img)){
echo '<div class="ks-img"><a href="'.esc_url($img).'"><img src="'.esc_url($img).'"></a></div>';
}

/* ACTIONS */

if($owner === $device_id){

echo '
<div class="ks-actions">
<form method="post">
<button class="ks-delete" name="ksiega_delete" value="'.$index.'">Usuń</button>
</form>
</div>
';

}

echo '</div>';
}

echo '</div>';

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

add_action('weselny_render_module_ksiega','weselny_ksiega_render');