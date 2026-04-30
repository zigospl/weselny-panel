<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =========================
   WŁĄCZENIE MODUŁU
========================= */

function weselny_galeria_option(){

$enabled = get_user_meta(get_current_user_id(),'weselny_modul_galeria',true);

?>

<p><strong>Zdjęcia od gości</strong></p>

<label>
<input type="checkbox" name="galeria_enabled" <?php checked($enabled,1); ?>>
</label>

<hr>

<?php

}

add_action('weselny_panel_features','weselny_galeria_option');


/* =========================
   ZAPIS
========================= */

function weselny_galeria_save(){

if(isset($_POST['weselny_features_save'])){

$enabled = isset($_POST['galeria_enabled']) ? 1 : 0;

update_user_meta(get_current_user_id(),'weselny_modul_galeria',$enabled);

}

}

add_action('init','weselny_galeria_save');


/* =========================
   KAFEL
========================= */

function weselny_galeria_tile(){

$enabled = get_user_meta(get_current_user_id(),'weselny_modul_galeria',true);

if($enabled){

echo '<div class="weselny-tile">';
echo '<a href="'.wc_get_account_endpoint_url('panel-wesela').'?galeria=1">Zdjęcia gości</a>';
echo '</div>';

}

}

add_action('weselny_panel_tiles','weselny_galeria_tile');


/* =========================
   PANEL
========================= */

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


/* =========================
   USUWANIE
========================= */

if(isset($_POST['usun_foto'])){

$index = intval($_POST['foto_index']);

unset($images[$index]);

$images = array_values($images);

update_post_meta($post_id,'weselny_galeria',$images);

}


/* =========================
   UI
========================= */

echo '<h2>Zdjęcia od gości</h2>';
echo '<p><a href="'.wc_get_account_endpoint_url('panel-wesela').'">← Powrót</a></p>';


/* =========================
   BRAK ZDJĘĆ
========================= */

if(empty($images)){

echo '<div style="
    text-align:center;
    padding:40px 20px;
    border:1px dashed #ccc;
    border-radius:10px;
    color:#666;
    margin-top:20px;
">
    <p style="font-size:18px;margin:0;">
    📸 W tym miejscu pojawią się zdjęcia dodane przez Waszych gości
    </p>
</div>';

return;

}


/* =========================
   GALERIA
========================= */

echo '<div style="
display:flex;
flex-wrap:wrap;
gap:12px;
margin-top:20px;
">';

foreach($images as $i=>$img){

$url = wp_get_attachment_url($img);
$thumb = wp_get_attachment_image_url($img,'medium');

echo '<div style="position:relative;">';

echo '<a href="'.$url.'" target="_blank">';
echo '<img src="'.$thumb.'" style="
    width:150px;
    height:150px;
    object-fit:cover;
    border-radius:10px;
">';
echo '</a>';

/* delete button */

echo '<form method="post" style="
position:absolute;
top:5px;
right:5px;
">';

echo '<input type="hidden" name="foto_index" value="'.$i.'">';

echo '<button name="usun_foto" style="
background:red;
color:#fff;
border:none;
padding:5px 8px;
cursor:pointer;
border-radius:5px;
">✕</button>';

echo '</form>';

echo '</div>';

}

echo '</div>';

}

add_action('woocommerce_account_panel-wesela_endpoint','weselny_panel_galeria');