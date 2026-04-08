<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =========================
   OPCJA WŁĄCZENIA
========================= */

function weselny_zadania_option(){

$enabled = get_user_meta(get_current_user_id(),'weselny_modul_zadania',true);

?>

<p><strong>Zadania dla gości</strong></p>

<label>
<input type="checkbox" name="zadania_enabled" <?php checked($enabled,1); ?>>

</label>

<hr>

<?php

}

add_action('weselny_panel_features','weselny_zadania_option');


/* =========================
   ZAPIS
========================= */

function weselny_zadania_save(){

if(isset($_POST['weselny_features_save'])){

$enabled = isset($_POST['zadania_enabled']) ? 1 : 0;
update_user_meta(get_current_user_id(),'weselny_modul_zadania',$enabled);

}

}

add_action('init','weselny_zadania_save');


/* =========================
   KAFLE
========================= */

function weselny_zadania_tiles(){

$enabled = get_user_meta(get_current_user_id(),'weselny_modul_zadania',true);

if(!$enabled) return;

$photos = get_user_meta(get_current_user_id(),'weselny_zadania_photos',true);

echo '<div  class="weselny-tile">';
echo '<a href="'.wc_get_account_endpoint_url('panel-wesela').'?zadania=1">Zadania</a>';
echo '</div>';

if($photos){
echo '<div class="weselny-tile">';
echo '<a href="'.wc_get_account_endpoint_url('panel-wesela').'?zadania-zdjecia=1">Zdjęcia z zadań</a>';
echo '</div>';
}

}

add_action('weselny_panel_tiles','weselny_zadania_tiles');


/* =========================
   PANEL
========================= */

function weselny_panel_zadania(){

if(!isset($_GET['zadania'])) return;

$user_id = get_current_user_id();

$wedding = get_posts([
'post_type'=>'wesela',
'meta_key'=>'user_id',
'meta_value'=>$user_id,
'posts_per_page'=>1
]);

if(!$wedding) return;

$post_id = $wedding[0]->ID;

$data = get_post_meta($post_id,'weselny_zadania',true);
if(!$data) $data = [];

$text = get_post_meta($post_id,'weselny_zadania_text',true);
$photos = get_user_meta($user_id,'weselny_zadania_photos',true);


/* ZAPIS */

if(isset($_POST['zapisz'])){

$text = sanitize_text_field($_POST['text']);
update_post_meta($post_id,'weselny_zadania_text',$text);

$photos = isset($_POST['photos']) ? 1 : 0;
update_user_meta($user_id,'weselny_zadania_photos',$photos);

$tasks = [];

if(isset($_POST['task'])){
foreach($_POST['task'] as $t){
if(trim($t)!=''){
$tasks[] = sanitize_text_field($t);
}
}
}

update_post_meta($post_id,'weselny_zadania',$tasks);

}


/* HTML */

echo '<h2>Zadania</h2>';
echo '<p><a href="'.wc_get_account_endpoint_url('panel-wesela').'">← Powrót</a></p>';

echo '<form method="post">';

echo '<textarea name="text" placeholder="Tekst powitalny" style="width:100%;">'.esc_textarea($text).'</textarea><br><br>';

echo '<label><input type="checkbox" name="photos" '.checked($photos,1,false).'> Wymagaj zdjęcia</label><br><br>';

$tasks = $data;

foreach($tasks as $i=>$t){
echo '<input type="text" name="task[]" value="'.esc_attr($t).'" placeholder="Zadanie"><br><br>';
}

echo '<button type="button" id="add-task">Dodaj zadanie</button><br><br>';

echo '<button name="zapisz">Zapisz</button>';

echo '</form>';


echo '
<script>
document.getElementById("add-task").addEventListener("click",function(){
let input = document.createElement("input");
input.name = "task[]";
input.placeholder = "Zadanie";
input.style.display = "block";
input.style.marginBottom = "10px";
this.before(input);
});
</script>
';

}

add_action('woocommerce_account_panel-wesela_endpoint','weselny_panel_zadania');

/* =========================
   PANEL ZDJĘĆ
========================= */

function weselny_panel_zadania_zdjecia(){

if(!isset($_GET['zadania-zdjecia'])) return;

$user_id = get_current_user_id();

$wedding = get_posts([
'post_type'=>'wesela',
'meta_key'=>'user_id',
'meta_value'=>$user_id,
'posts_per_page'=>1
]);

if(!$wedding) return;

$post_id = $wedding[0]->ID;

$data = get_post_meta($post_id,'weselny_zadania_photos_data',true);

if(!$data) $data = [];

echo '<h2>Zdjęcia z zadań</h2>';
echo '<p><a href="'.wc_get_account_endpoint_url('panel-wesela').'">← Powrót</a></p>';

foreach($data as $device => $photos){

echo '<div style="border:1px solid #ccc;padding:15px;margin-bottom:20px;">';

echo '<h3>Urządzenie: '.$device.'</h3>';

echo '<div style="display:flex;flex-wrap:wrap;gap:10px;">';

foreach($photos as $p){

$url = wp_get_attachment_url($p['id']);

echo '<div>';
echo '<img src="'.$url.'" style="width:120px;height:120px;object-fit:cover;"><br>';
echo '<small>'.$p['task'].'</small>';
echo '</div>';

}

echo '</div>';

echo '</div>';

}

}

add_action('woocommerce_account_panel-wesela_endpoint','weselny_panel_zadania_zdjecia');