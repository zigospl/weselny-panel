<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* włączenie modułu */

function weselny_galeria_option(){

$enabled = get_user_meta(get_current_user_id(),'weselny_modul_galeria',true);

?>

<p><strong>Zdjęcia od gości</strong></p>

<label>
<input type="checkbox" name="galeria_enabled" <?php checked($enabled,1); ?>>
Włącz
</label>

<hr>

<?php

}

add_action('weselny_panel_features','weselny_galeria_option');


/* zapis ustawienia */

function weselny_galeria_save(){

if(isset($_POST['weselny_features_save'])){

$enabled = isset($_POST['galeria_enabled']) ? 1 : 0;

update_user_meta(get_current_user_id(),'weselny_modul_galeria',$enabled);

}

}

add_action('init','weselny_galeria_save');


/* kafelek */

function weselny_galeria_tile(){

$enabled = get_user_meta(get_current_user_id(),'weselny_modul_galeria',true);

if($enabled){

echo '<div style="border:1px solid #ccc;padding:20px;display:inline-block;margin:10px;">';
echo '<a href="'.wc_get_account_endpoint_url('panel-wesela').'?galeria=1">Zdjęcia gości</a>';
echo '</div>';

}

}

add_action('weselny_panel_tiles','weselny_galeria_tile');


/* panel klienta */

function weselny_panel_galeria(){

if(!isset($_GET['galeria'])) return;

$user_id = get_current_user_id();

$wedding = get_posts(array(
'post_type'=>'wesela',
'meta_key'=>'user_id',
'meta_value'=>$user_id,
'posts_per_page'=>1
));

if(!$wedding) return;

$post_id = $wedding[0]->ID;

$images = get_post_meta($post_id,'weselny_galeria',true);

if(!$images) $images = array();


/* usuwanie z panelu */

if(isset($_POST['usun_foto'])){

$index = intval($_POST['foto_index']);

unset($images[$index]);

$images = array_values($images);

update_post_meta($post_id,'weselny_galeria',$images);

}


/* wyświetlanie */

echo '<h2>Zdjęcia od gości</h2>';

foreach($images as $i=>$img){

$url = wp_get_attachment_url($img);

$thumb = wp_get_attachment_image_url($img,'thumbnail');

echo '<div style="display:inline-block;margin:10px;text-align:center;">';

echo '<a href="'.$url.'">';
echo '<img src="'.$thumb.'" style="width:120px;height:120px;object-fit:cover;">';
echo '</a>';

echo '<form method="post">';
echo '<input type="hidden" name="foto_index" value="'.$i.'">';
echo '<button name="usun_foto">Usuń</button>';
echo '</form>';

echo '</div>';

}

}

add_action('woocommerce_account_panel-wesela_endpoint','weselny_panel_galeria');