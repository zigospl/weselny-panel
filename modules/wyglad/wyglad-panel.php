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

/* 🔥 DEFAULT PARTICLES */
if(!isset($data['particles_enabled'])){
    $data['particles_enabled'] = 1;
}

$banner_id = $data['banner_id'] ?? '';
$banner_url = $banner_id ? wp_get_attachment_url($banner_id) : '';


/* =========================
   DOMYŚLNE KOLORY
========================= */

$defaults = [
'color_primary' => '#ffffff',
'color_tile' => '#fff',
'color_tile_hover' => '#192e03',
'color_text' => '#000000',
'color_headings' => '#000000'
];


/* =========================
   RESET KOLORÓW
========================= */

if(isset($_POST['reset_colors'])){

foreach($defaults as $key => $val){
$data[$key] = $val;
}

update_user_meta($user_id,'weselny_wyglad',$data);

$saved = true;

}


/* =========================
   USUWANIE
========================= */

if(isset($_POST['usun_banner'])){
$data['banner_id'] = '';
update_user_meta($user_id,'weselny_wyglad',$data);
}


/* =========================
   UPLOAD
========================= */

if(!empty($_FILES['banner_file']['tmp_name'])){

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


/* =========================
   ZAPIS
========================= */

$saved = false;

if(isset($_POST['zapisz_wyglad'])){

$data['naglowek'] = sanitize_text_field($_POST['naglowek'] ?? '');
$data['podpis'] = sanitize_text_field($_POST['podpis'] ?? '');
$data['kosciol'] = sanitize_text_field($_POST['kosciol'] ?? '');
$data['miejsce'] = sanitize_text_field($_POST['miejsce'] ?? '');

$data['color_primary'] = sanitize_hex_color($_POST['color_primary'] ?? '');
$data['color_tile'] = sanitize_hex_color($_POST['color_tile'] ?? '');
$data['color_tile_hover'] = sanitize_hex_color($_POST['color_tile_hover'] ?? '');
$data['color_text'] = sanitize_hex_color($_POST['color_text'] ?? '');
$data['color_headings'] = sanitize_hex_color($_POST['color_headings'] ?? '');

/* 🔥 PARTICLES */
$data['particles_enabled'] = isset($_POST['particles_enabled']) ? 1 : 0;

update_user_meta($user_id,'weselny_wyglad',$data);

$saved = true;

}


/* =========================
   HTML
========================= */

echo '<h2>Wygląd panelu</h2>';
echo '<p><a href="'.wc_get_account_endpoint_url('panel-wesela').'">← Powrót</a></p>';

echo '<div id="weselny-save-msg" style="
display:none;
background:#d4edda;
color:#155724;
padding:10px 15px;
margin-bottom:15px;
border-radius:6px;
font-weight:500;
">
Zapisano
</div>';

echo '<form method="post" enctype="multipart/form-data">';


/* BANNER */

echo '<p><strong>Banner</strong></p>';

if($banner_url){
echo '<img src="'.$banner_url.'" style="width:200px;height:120px;object-fit:cover;"><br>';
echo '<button name="usun_banner">Usuń banner</button><br><br>';
}

echo '<input type="file" name="banner_file"><br><br>';


/* TEKSTY */

echo '<div class="wyglad-inputs">';

echo '<input type="text" name="naglowek" placeholder="Nagłówek" value="'.esc_attr($data['naglowek'] ?? '').'"><br><br>';
echo '<input type="text" name="podpis" placeholder="Podpis" value="'.esc_attr($data['podpis'] ?? '').'"><br><br>';

echo '</div>';


/* 🔥 PARTICLES SWITCH */

echo '<h3>Efekty</h3>';

echo '<label style="display:flex;align-items:center;gap:8px;margin-bottom:15px;">';
echo '<input type="checkbox" name="particles_enabled" '.checked($data['particles_enabled'],1,false).'>';
echo 'Włącz animację tła (particles)';
echo '</label>';


/* KOLORY */

echo '<h3>Kolory</h3>';

echo 'Kolor główny:<br>';
echo '<input style="padding:0px;" type="color" name="color_primary" value="'.esc_attr($data['color_primary'] ?? $defaults['color_primary']).'"><br><br>';

echo 'Kolor kafelków:<br>';
echo '<input style="padding:0px;" type="color" name="color_tile" value="'.esc_attr($data['color_tile'] ?? $defaults['color_tile']).'"><br><br>';

echo 'Kolor hover:<br>';
echo '<input style="padding:0px;" type="color" name="color_tile_hover" value="'.esc_attr($data['color_tile_hover'] ?? $defaults['color_tile_hover']).'"><br><br>';

echo 'Kolor tekstu:<br>';
echo '<input style="padding:0px;" type="color" name="color_text" value="'.esc_attr($data['color_text'] ?? $defaults['color_text']).'"><br><br>';

echo 'Kolor nagłówków:<br>';
echo '<input style="padding:0px;" type="color" name="color_headings" value="'.esc_attr($data['color_headings'] ?? $defaults['color_headings']).'"><br><br>';


echo '<button name="zapisz_wyglad">Zapisz</button> ';
echo '<button name="reset_colors" style="margin-left:10px;background:#ccc;">Przywróć domyślne kolory</button>';

echo '</form>';
?>

<script>
<?php if(!empty($saved)): ?>

document.addEventListener("DOMContentLoaded", function(){

let msg = document.getElementById("weselny-save-msg");

msg.style.display = "block";
msg.style.opacity = "0";
msg.style.transition = "opacity 0.3s";

setTimeout(()=>{ msg.style.opacity = "1"; }, 50);

setTimeout(()=>{
msg.style.opacity = "0";
setTimeout(()=>{ msg.style.display = "none"; }, 300);
}, 2000);

});

<?php endif; ?>
</script>

<?php

}

add_action('woocommerce_account_panel-wesela_endpoint','weselny_panel_wyglad');