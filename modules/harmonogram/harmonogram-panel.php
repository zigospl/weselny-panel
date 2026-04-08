<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =========================
   OPCJA WŁĄCZENIA
========================= */

function weselny_harmonogram_option(){

$enabled = get_user_meta(get_current_user_id(),'weselny_modul_harmonogram',true);

?>

<p><strong>Harmonogram</strong></p>

<label>
<input type="checkbox" name="harmonogram_enabled" <?php checked($enabled,1); ?>>
</label>

<hr>

<?php

}

add_action('weselny_panel_features','weselny_harmonogram_option');


/* =========================
   ZAPIS WŁĄCZENIA
========================= */

function weselny_harmonogram_save(){

if(isset($_POST['weselny_features_save'])){

$enabled = isset($_POST['harmonogram_enabled']) ? 1 : 0;

update_user_meta(get_current_user_id(),'weselny_modul_harmonogram',$enabled);

}

}

add_action('init','weselny_harmonogram_save');


/* =========================
   KAFEL
========================= */

function weselny_harmonogram_tile(){

$enabled = get_user_meta(get_current_user_id(),'weselny_modul_harmonogram',true);

if($enabled){

echo '<div class="weselny-tile">';
echo '<a href="'.wc_get_account_endpoint_url('panel-wesela').'?harmonogram=1">Harmonogram</a>';
echo '</div>';

}

}

add_action('weselny_panel_tiles','weselny_harmonogram_tile');


/* =========================
   PANEL KLIENTA
========================= */

function weselny_panel_harmonogram(){

if(!isset($_GET['harmonogram'])) return;

$user_id = get_current_user_id();

$wedding = get_posts([
'post_type'=>'wesela',
'meta_key'=>'user_id',
'meta_value'=>$user_id,
'posts_per_page'=>1
]);

if(!$wedding) return;

$post_id = $wedding[0]->ID;

$data = get_post_meta($post_id,'weselny_harmonogram',true);
if(!$data) $data = [];


/* =========================
   REDIRECT
========================= */

function weselny_harmonogram_redirect(){
    wp_redirect(add_query_arg('harmonogram','1', wc_get_account_endpoint_url('panel-wesela')));
    exit;
}


/* =========================
   DODAJ POZYCJĘ
========================= */

if(isset($_POST['dodaj'])){

$data[] = [
'title' => '',
'time' => '',
'description' => ''
];

update_post_meta($post_id,'weselny_harmonogram',$data);

weselny_harmonogram_redirect();

}


/* =========================
   USUŃ
========================= */

if(isset($_POST['usun'])){

$index = intval($_POST['usun']);

unset($data[$index]);
$data = array_values($data);

update_post_meta($post_id,'weselny_harmonogram',$data);

weselny_harmonogram_redirect();

}


/* =========================
   ZAPIS
========================= */

if(isset($_POST['zapisz'])){

foreach($data as $i=>$row){

$data[$i]['title'] = sanitize_text_field($_POST['title'][$i] ?? '');
$data[$i]['time'] = sanitize_text_field($_POST['time'][$i] ?? '');
$data[$i]['description'] = wp_kses_post($_POST['description'][$i] ?? '');

}

update_post_meta($post_id,'weselny_harmonogram',$data);

weselny_harmonogram_redirect();

}


/* =========================
   HTML
========================= */

echo '<h2>Harmonogram</h2>';
echo '<p><a href="'.wc_get_account_endpoint_url('panel-wesela').'">← Powrót</a></p>';

echo '<form method="post">';

foreach($data as $i=>$row){

echo '<h3>Pozycja '.($i+1).'</h3>';

echo '<input class="text-input-block" type="text" name="title[]" placeholder="Nagłówek" value="'.esc_attr($row['title']).'"><br><br>';
echo '<input class="text-input-block" type="text" name="time[]" placeholder="Czas (np. 16:00)" value="'.esc_attr($row['time']).'"><br><br>';

wp_editor(
    $row['description'],
    'description_'.$i,
    [
        'textarea_name' => 'description[]',
        'textarea_rows' => 5
    ]
);

echo '<br>';
echo '<button name="usun" value="'.$i.'">Usuń</button>';

echo '<hr>';

}

echo '<button name="dodaj">Dodaj pozycję</button><br><br>';
echo '<button name="zapisz">Zapisz</button>';

echo '</form>';

}

add_action('woocommerce_account_panel-wesela_endpoint','weselny_panel_harmonogram');