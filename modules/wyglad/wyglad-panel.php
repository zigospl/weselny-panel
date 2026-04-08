<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =========================
   KAFEL
========================= */

function weselny_wyglad_tile(){

echo '<div class="weselny-tile">';
echo '<a href="'.wc_get_account_endpoint_url('panel-wesela').'?wyglad=1">Wygląd panelu</a>';
echo '</div>';

}

add_action('weselny_panel_tiles','weselny_wyglad_tile');


/* =========================
   PANEL
========================= */

function weselny_panel_wyglad(){

if(!isset($_GET['wyglad'])) return;

$user_id = get_current_user_id();

$data = get_user_meta($user_id,'weselny_wyglad',true);
if(!$data) $data = [];

$banner_id = $data['banner_id'] ?? '';
$banner_url = $banner_id ? wp_get_attachment_url($banner_id) : '';


/* USUWANIE */

if(isset($_POST['usun_banner'])){
$data['banner_id'] = '';
update_user_meta($user_id,'weselny_wyglad',$data);
}


/* UPLOAD */

if(isset($_FILES['banner_file']) && $_FILES['banner_file']['tmp_name']){

require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');

$file = $_FILES['banner_file'];

$upload = wp_handle_upload($file,array('test_form'=>false));

if(!isset($upload['error'])){

$attachment = array(
'post_mime_type'=>$upload['type'],
'post_title'=>sanitize_file_name($upload['file']),
'post_status'=>'inherit'
);

$id = wp_insert_attachment($attachment,$upload['file']);

$attach_data = wp_generate_attachment_metadata($id,$upload['file']);
wp_update_attachment_metadata($id,$attach_data);

$data['banner_id'] = $id;

update_user_meta($user_id,'weselny_wyglad',$data);

}

}


/* ZAPIS TEKSTÓW */

if(isset($_POST['zapisz_wyglad'])){

$data['naglowek'] = sanitize_text_field($_POST['naglowek']);
$data['podpis'] = sanitize_text_field($_POST['podpis']);
$data['kosciol'] = sanitize_text_field($_POST['kosciol']);
$data['miejsce'] = sanitize_text_field($_POST['miejsce']);

update_user_meta($user_id,'weselny_wyglad',$data);

}


/* HTML */

echo '<h2>Wygląd panelu</h2>';
echo '<p><a href="'.wc_get_account_endpoint_url('panel-wesela').'">← Powrót</a></p>';

echo '<form method="post" enctype="multipart/form-data">';

/* BANNER */

echo '<p><strong>Banner</strong></p>';

if($banner_url){

echo '<img src="'.$banner_url.'" style="width:200px;height:120px;object-fit:cover;"><br>';
echo '<button name="usun_banner">Usuń banner</button><br><br>';

}

echo '<input type="file" name="banner_file"><br><br>';

/* TEKSTY */

echo '<input type="text" name="naglowek" placeholder="Nagłówek" value="'.esc_attr($data['naglowek'] ?? '').'"><br><br>';
echo '<input type="text" name="podpis" placeholder="Podpis" value="'.esc_attr($data['podpis'] ?? '').'"><br><br>';
echo '<input type="text" name="kosciol" placeholder="Kościół" value="'.esc_attr($data['kosciol'] ?? '').'"><br><br>';
echo '<input type="text" name="miejsce" placeholder="Miejsce wesela" value="'.esc_attr($data['miejsce'] ?? '').'"><br><br>';

echo '<button name="zapisz_wyglad">Zapisz</button>';

echo '</form>';

}

add_action('woocommerce_account_panel-wesela_endpoint','weselny_panel_wyglad');