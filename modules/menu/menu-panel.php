<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =========================
   OPCJA WŁĄCZENIA
========================= */

function weselny_menu_option(){

$enabled = get_user_meta(get_current_user_id(),'weselny_modul_menu',true);

?>

<p><strong>Menu weselne</strong></p>

<label>
<input type="checkbox" name="menu_enabled" <?php checked($enabled,1); ?>>
Włącz
</label>

<hr>

<?php

}

add_action('weselny_panel_features','weselny_menu_option');


/* =========================
   ZAPIS WŁĄCZENIA
========================= */

function weselny_menu_save(){

if(isset($_POST['weselny_features_save'])){

$enabled = isset($_POST['menu_enabled']) ? 1 : 0;

update_user_meta(get_current_user_id(),'weselny_modul_menu',$enabled);

}

}

add_action('init','weselny_menu_save');


/* =========================
   KAFEL
========================= */

function weselny_menu_tile(){

$enabled = get_user_meta(get_current_user_id(),'weselny_modul_menu',true);

if($enabled){

echo '<div style="border:1px solid #ccc;padding:20px;display:inline-block;margin:10px;">';
echo '<a href="'.wc_get_account_endpoint_url('panel-wesela').'?menu=1">Menu weselne</a>';
echo '</div>';

}

}

add_action('weselny_panel_tiles','weselny_menu_tile');


/* =========================
   PANEL KLIENTA
========================= */

function weselny_panel_menu(){

if(!isset($_GET['menu'])) return;

$user_id = get_current_user_id();

$wedding = get_posts([
'post_type'=>'wesela',
'meta_key'=>'user_id',
'meta_value'=>$user_id,
'posts_per_page'=>1
]);

if(!$wedding) return;

$post_id = $wedding[0]->ID;

$data = get_post_meta($post_id,'weselny_menu',true);
if(!$data) $data = [];


/* DODAJ SEKCJĘ */

if(isset($_POST['dodaj_sekcje'])){

$data[] = [
'title'=>'',
'content'=>''
];

update_post_meta($post_id,'weselny_menu',$data);

}


/* USUŃ SEKCJĘ */

if(isset($_POST['usun_sekcje'])){

$index = intval($_POST['index']);

unset($data[$index]);
$data = array_values($data);

update_post_meta($post_id,'weselny_menu',$data);

}


/* ZAPIS */

if(isset($_POST['zapisz_menu'])){

foreach($data as $i=>$section){

$data[$i]['title'] = sanitize_text_field($_POST['title'][$i]);
$data[$i]['content'] = wp_kses_post($_POST['content'][$i]);

}

update_post_meta($post_id,'weselny_menu',$data);

echo "<script>alert('Zapisano menu');</script>";

}


/* WYŚWIETLANIE */

echo '<h2>Menu weselne</h2>';
echo '<p><a href="'.wc_get_account_endpoint_url('panel-wesela').'">← Powrót</a></p>';

echo '<form method="post">';

foreach($data as $i=>$section){

echo '<h3>Sekcja '.($i+1).'</h3>';

echo '<input type="text" name="title[]" value="'.esc_attr($section['title']).'" placeholder="Nagłówek"><br><br>';

wp_editor(
$section['content'],
'content_'.$i,
[
'textarea_name'=>'content[]',
'textarea_rows'=>5
]
);

echo '<br>';

echo '<button name="usun_sekcje" value="1">Usuń</button>';
echo '<input type="hidden" name="index" value="'.$i.'">';

echo '<hr>';

}

echo '<button name="dodaj_sekcje">Dodaj sekcję</button><br><br>';
echo '<button name="zapisz_menu">Zapisz</button>';

echo '</form>';

}

add_action('woocommerce_account_panel-wesela_endpoint','weselny_panel_menu');