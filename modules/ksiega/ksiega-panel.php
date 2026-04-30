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


/* STYLE */

echo '
<style>
.ksiega-empty{
text-align:center;
padding:40px 20px;
border:2px dashed #ddd;
border-radius:12px;
color:#666;
margin-top:20px;
background:#fafafa;
}

.ksiega-card{
border:1px solid #ddd;
padding:20px;
margin-bottom:15px;
border-radius:12px;
background:#fff;
box-shadow:0 4px 10px rgba(0,0,0,0.05);
}

.ksiega-card strong{
font-size:16px;
}

.ksiega-img-link{
display:inline-block;
margin-top:10px;
}

.ksiega-img-link img{
width:120px;
border-radius:8px;
cursor:zoom-in;
transition:0.2s;
}

.ksiega-img-link img:hover{
transform:scale(1.05);
}

.ksiega-delete{
margin-top:10px;
background:#e74c3c;
color:#fff;
border:none;
padding:8px 12px;
border-radius:6px;
cursor:pointer;
}

.ksiega-delete:hover{
background:#c0392b;
}
</style>
';


/* WYŚWIETLANIE */

echo '<h2>Księga gości</h2>';
echo '<p><a href="'.wc_get_account_endpoint_url('panel-wesela').'">← Powrót</a></p>';


/* EMPTY STATE */

if(empty($data)){

echo '
<div class="ksiega-empty">
<p style="font-size:20px;margin-bottom:10px;">💌</p>
<p><strong>Tu pojawią się wpisy od Waszych gości</strong></p>
<p style="margin-top:10px;">
Gdy goście zaczną dodawać życzenia i zdjęcia,
zobaczysz je właśnie w tym miejscu.
</p>
</div>
';

return;
}


/* LISTA WPISÓW */

foreach($data as $i=>$entry){

echo '<div class="ksiega-card">';

echo '<strong>'.esc_html($entry['name']).'</strong><br><br>';

echo wp_kses_post($entry['content']).'<br>';

if(!empty($entry['img'])){
echo '
<a href="'.esc_url($entry['img']).'" class="ksiega-img-link" target="_blank" rel="noopener">
<img src="'.esc_url($entry['img']).'">
</a>
';
}

echo '<form method="post">';
echo '<input type="hidden" name="index" value="'.$i.'">';
echo '<button name="usun" class="ksiega-delete">Usuń</button>';
echo '</form>';

echo '</div>';

}

}

add_action('woocommerce_account_panel-wesela_endpoint','weselny_panel_ksiega');