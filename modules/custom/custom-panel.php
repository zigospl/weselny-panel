<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* =========================
   KAFEL
========================= */

function weselny_custom_tile(){
    echo '<div class="weselny-tile">';
    echo '<a href="'.wc_get_account_endpoint_url('panel-wesela').'?custom=1">Własne sekcje</a>';
    echo '</div>';
}
add_action('weselny_panel_tiles','weselny_custom_tile');


/* =========================
   HELPER – REDIRECT
========================= */

function weselny_custom_redirect(){
    wp_redirect(add_query_arg('custom','1', wc_get_account_endpoint_url('panel-wesela')));
    exit;
}


/* =========================
   HELPER – MERGE BLOCKS
========================= */

function weselny_merge_blocks($existing, $posted){

    if(!$posted) return $existing;

    $result = [];

    foreach($posted as $i => $b){

        $type = $b['type'] ?? '';
        $new_value = $b['value'] ?? '';
        $old_value = $existing[$i]['value'] ?? '';

        $result[$i] = [
            'type' => $type,
            'value' => $new_value !== '' ? $new_value : $old_value
        ];
    }

    return $result;
}


/* =========================
   PANEL
========================= */

function weselny_custom_panel(){

if(!isset($_GET['custom'])) return;

$user_id = get_current_user_id();

$wedding = get_posts([
    'post_type'=>'wesela',
    'meta_key'=>'user_id',
    'meta_value'=>$user_id,
    'posts_per_page'=>1
]);

if(!$wedding) return;

$post_id = $wedding[0]->ID;

$data = get_post_meta($post_id,'weselny_custom_modules',true);
if(!$data) $data = [];


/* =========================
   ADD MODULE
========================= */

if(isset($_POST['add_module'])){
    $data[] = ['title'=>'','blocks'=>[]];
    update_post_meta($post_id,'weselny_custom_modules',$data);
    weselny_custom_redirect();
}


/* =========================
   DELETE MODULE
========================= */

if(isset($_POST['delete_module'])){
    $i = intval($_POST['index']);
    unset($data[$i]);
    $data = array_values($data);
    update_post_meta($post_id,'weselny_custom_modules',$data);
    weselny_custom_redirect();
}


/* =========================
   ADD BLOCK
========================= */

if(isset($_POST['add_block'])){

    $i = intval($_POST['index']);

    $data[$i]['blocks'] = weselny_merge_blocks(
        $data[$i]['blocks'] ?? [],
        $_POST['blocks'] ?? []
    );

    $data[$i]['blocks'][] = [
        'type'=>$_POST['type'],
        'value'=>''
    ];

    update_post_meta($post_id,'weselny_custom_modules',$data);
    weselny_custom_redirect();
}


/* =========================
   DELETE BLOCK
========================= */

if(isset($_POST['delete_block'])){

    $i = intval($_POST['index']);
    $k = intval($_POST['delete_block']);

    $data[$i]['blocks'] = weselny_merge_blocks(
        $data[$i]['blocks'] ?? [],
        $_POST['blocks'] ?? []
    );

    unset($data[$i]['blocks'][$k]);
    $data[$i]['blocks'] = array_values($data[$i]['blocks']);

    update_post_meta($post_id,'weselny_custom_modules',$data);
    weselny_custom_redirect();
}


/* =========================
   UPLOAD IMAGE
========================= */

if(isset($_POST['upload_image']) && !empty($_FILES['block_image']['tmp_name'])){

    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $i = intval($_POST['index']);
    $k = intval($_POST['upload_image']);

    $data[$i]['blocks'] = weselny_merge_blocks(
        $data[$i]['blocks'] ?? [],
        $_POST['blocks'] ?? []
    );

    $upload = wp_handle_upload($_FILES['block_image'],['test_form'=>false]);

    if(!isset($upload['error'])){
        $data[$i]['blocks'][$k]['value'] = $upload['url'];
        update_post_meta($post_id,'weselny_custom_modules',$data);
    }

    weselny_custom_redirect();
}


/* =========================
   DELETE IMAGE
========================= */

if(isset($_POST['delete_image'])){

    $i = intval($_POST['index']);
    $k = intval($_POST['delete_image']);

    $data[$i]['blocks'] = weselny_merge_blocks(
        $data[$i]['blocks'] ?? [],
        $_POST['blocks'] ?? []
    );

    $data[$i]['blocks'][$k]['value'] = '';

    update_post_meta($post_id,'weselny_custom_modules',$data);
    weselny_custom_redirect();
}


/* =========================
   SAVE MODULE
========================= */

if(isset($_POST['save_module'])){

    $i = intval($_POST['index']);

    $data[$i]['title'] = sanitize_text_field($_POST['title']);

    $data[$i]['blocks'] = weselny_merge_blocks(
        $data[$i]['blocks'] ?? [],
        $_POST['blocks'] ?? []
    );

    update_post_meta($post_id,'weselny_custom_modules',$data);
    weselny_custom_redirect();
}


/* =========================
   UI
========================= */

echo '<h2>Własne sekcje</h2>';
echo '<p><a href="'.wc_get_account_endpoint_url('panel-wesela').'">← Powrót</a></p>';

echo '<form method="post"><button name="add_module">Dodaj sekcję</button></form><hr>';

foreach($data as $i=>$mod){

echo '<div style="border:1px solid #ccc;padding:15px;margin-bottom:20px;">';

echo '<form method="post" enctype="multipart/form-data">';
echo '<input type="hidden" name="index" value="'.$i.'">';

echo '<input type="text" name="title" value="'.esc_attr($mod['title'] ?? '').'"><br><br>';

if(!empty($mod['blocks'])){
foreach($mod['blocks'] as $k=>$b){

$type = $b['type'] ?? '';
$value = $b['value'] ?? '';

echo '<div style="border:1px dashed #aaa;padding:10px;margin-bottom:10px;">';

echo '<strong>'.$type.'</strong>';
echo '<input type="hidden" name="blocks['.$k.'][type]" value="'.$type.'">';
echo '<button name="delete_block" value="'.$k.'">X</button><br><br>';

if($type=='text'){

wp_editor(
    $value,
    'editor_'.$i.'_'.$k,
    [
        'textarea_name' => 'blocks['.$k.'][value]',
        'textarea_rows' => 6
    ]
);

}elseif($type=='img'){

echo '<input type="hidden" name="blocks['.$k.'][value]" value="'.esc_attr($value).'">';

if($value){
echo '<img src="'.$value.'" style="width:120px;">';
echo '<button name="delete_image" value="'.$k.'">Usuń</button>';
}

echo '<input type="file" name="block_image">';
echo '<button name="upload_image" value="'.$k.'">Wyślij</button>';

}else{

echo '<input type="text" name="blocks['.$k.'][value]" value="'.$value.'">';

}

echo '</div>';
}
}

echo '
<select name="type">
<option value="h1">Duży nagłówek</option>
<option value="h2">Mały nagłówek</option>
<option value="text">Tekst</option>
<option value="img">Obrazek</option>
</select>
<button name="add_block">Dodaj blok</button><br><br>
';

echo '<button name="save_module">Zapisz</button>';
echo '<button name="delete_module">Usuń sekcję</button>';

echo '</form>';
echo '</div>';
}

}

add_action('woocommerce_account_panel-wesela_endpoint','weselny_custom_panel');