<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/* =========================
   OPCJA
========================= */

function weselny_ksiega_option(){

$enabled = get_user_meta(get_current_user_id(),'weselny_modul_ksiega',true);
?>

<p><strong>Księga gości</strong></p>

<label>
<input type="checkbox" name="ksiega_enabled" <?php checked($enabled,1); ?>>

</label>

<hr>

<?php
}

add_action('weselny_panel_features','weselny_ksiega_option');


/* =========================
   ZAPIS
========================= */

function weselny_ksiega_save(){

if(isset($_POST['weselny_features_save'])){

$val = isset($_POST['ksiega_enabled']) ? 1 : 0;

update_user_meta(get_current_user_id(),'weselny_modul_ksiega',$val);

}
}

add_action('init','weselny_ksiega_save');


/* =========================
   KAFEL
========================= */

function weselny_ksiega_tile(){

$enabled = get_user_meta(get_current_user_id(),'weselny_modul_ksiega',true);

if(!$enabled) return;

echo '<div class="weselny-tile">';
echo '<a href="'.wc_get_account_endpoint_url('panel-wesela').'?ksiega=1">Księga gości</a>';
echo '</div>';

}

add_action('weselny_panel_tiles','weselny_ksiega_tile');


/* =========================
   PANEL
========================= */

function weselny_panel_ksiega(){

if(!isset($_GET['ksiega'])) return;

$user_id = get_current_user_id();

$wedding = get_posts([
'post_type'=>'wesela',
'meta_key'=>'user_id',
'meta_value'=>$user_id,
'posts_per_page'=>1
]);

if(!$wedding) return;

$post_id = $wedding[0]->ID;

$data = get_post_meta($post_id,'weselny_ksiega',true);
if(!$data) $data = [];


/* USUWANIE */

if(isset($_POST['usun'])){

$i = intval($_POST['index']);

unset($data[$i]);
$data = array_values($data);

update_post_meta($post_id,'weselny_ksiega',$data);

}


/* WYŚWIETLANIE */

echo '<h2>Księga gości</h2>';
echo '<p><a href="'.wc_get_account_endpoint_url('panel-wesela').'">← Powrót</a></p>';

foreach($data as $i=>$entry){

echo '<div style="border:1px solid #ccc;padding:15px;margin-bottom:15px;">';

echo '<strong>'.$entry['name'].'</strong><br><br>';

echo $entry['content'].'<br>';

if(!empty($entry['img'])){
echo '<img src="'.$entry['img'].'" style="width:120px;margin-top:10px;"><br>';
}

echo '<form method="post">';
echo '<input type="hidden" name="index" value="'.$i.'">';
echo '<button name="usun">Usuń</button>';
echo '</form>';

echo '</div>';

}

}

add_action('woocommerce_account_panel-wesela_endpoint','weselny_panel_ksiega');