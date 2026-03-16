<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function weselny_galeria_guest_module($content){

if(get_post_type()!=='wesela' || !is_singular('wesela')) return $content;

$post_id = get_the_ID();

/* właściciel wesela */

$user_id = get_post_meta($post_id,'user_id',true);

if(!$user_id) return $content;

/* czy moduł włączony */

$enabled = get_user_meta($user_id,'weselny_modul_galeria',true);

if(!$enabled) return $content;


/* OTWARTA GALERIA */

if(isset($_GET['galeria'])){

if(isset($_FILES['photos'])){

require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');

$files = $_FILES['photos'];

$ids = get_post_meta($post_id,'weselny_galeria',true);

if(!$ids) $ids = array();

foreach($files['name'] as $k=>$name){

if($files['tmp_name'][$k]){

$file = array(
'name'=>$files['name'][$k],
'type'=>$files['type'][$k],
'tmp_name'=>$files['tmp_name'][$k],
'error'=>$files['error'][$k],
'size'=>$files['size'][$k]
);

$upload = wp_handle_upload($file,array('test_form'=>false));

if(!isset($upload['error'])){

$attachment = array(
'post_mime_type'=>$upload['type'],
'post_title'=>sanitize_file_name($upload['file']),
'post_status'=>'inherit'
);

$id = wp_insert_attachment($attachment,$upload['file']);

require_once(ABSPATH.'wp-admin/includes/image.php');

$attach_data = wp_generate_attachment_metadata($id,$upload['file']);

wp_update_attachment_metadata($id,$attach_data);

$ids[]=$id;

}

}

}

update_post_meta($post_id,'weselny_galeria',$ids);

echo "<script>alert('Zdjęcia przesłane');</script>";

}

$html = '<p><a href="'.get_permalink().'">← Powrót do panelu</a></p>';

$html .= '<h2>Dodaj zdjęcia</h2>';

$html .= '
<form method="post" enctype="multipart/form-data">

<input type="file" name="photos[]" accept="image/*" capture="environment" multiple>

<br><br>

<button>Prześlij</button>

</form>
';

return $html;

}


/* PANEL KAFELKÓW */

$url = add_query_arg('galeria','1',get_permalink());

$html = '<div style="border:1px solid #ccc;padding:20px;display:inline-block;margin:10px;">';
$html .= '<a href="'.$url.'">Dodaj zdjęcia</a>';
$html .= '</div>';

return $content.$html;

}

add_filter('the_content','weselny_galeria_guest_module');