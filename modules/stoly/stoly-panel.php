<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =========================
   OPCJA
========================= */

function weselny_stoly_option(){

$enabled = get_user_meta(get_current_user_id(),'weselny_modul_stoly',true);
?>

<p><strong>Lista stołów</strong></p>

<label>
<input type="checkbox" name="stoly_enabled" <?php checked($enabled,1); ?>>
</label>

<hr>

<?php
}
add_action('weselny_panel_features','weselny_stoly_option');


/* =========================
   ZAPIS OPCJI
========================= */

function weselny_stoly_save(){

if(isset($_POST['weselny_features_save'])){
$enabled = isset($_POST['stoly_enabled']) ? 1 : 0;
update_user_meta(get_current_user_id(),'weselny_modul_stoly',$enabled);
}

}
add_action('init','weselny_stoly_save');


/* =========================
   KAFEL
========================= */

function weselny_stoly_tile(){

$enabled = get_user_meta(get_current_user_id(),'weselny_modul_stoly',true);

if($enabled){
echo '<div class="weselny-tile">';
echo '<a href="'.wc_get_account_endpoint_url('panel-wesela').'?stoly=1">Lista stołów</a>';
echo '</div>';
}

}
add_action('weselny_panel_tiles','weselny_stoly_tile');


/* =========================
   PANEL
========================= */

function weselny_panel_stoly(){

if(!isset($_GET['stoly'])) return;

$user_id = get_current_user_id();

$wedding = get_posts([
'post_type'=>'wesela',
'meta_key'=>'user_id',
'meta_value'=>$user_id,
'posts_per_page'=>1
]);

if(!$wedding) return;

$post_id = $wedding[0]->ID;

$data = get_post_meta($post_id,'weselny_stoly',true);
if(!$data) $data = [];

$search_enabled = get_post_meta($post_id,'weselny_stoly_search',true);


/* =========================
   ZAPIS
========================= */

$saved = false;

if(isset($_POST['save_all'])){

$new_data = [];

if(isset($_POST['tables'])){
foreach($_POST['tables'] as $table){

$title = sanitize_text_field($table['title'] ?? '');

$guests = [];

if(!empty($table['guest_name'])){
foreach($table['guest_name'] as $k=>$name){

if(trim($name)!=''){
$guests[] = [
'name'=>sanitize_text_field($name),
'seat'=>sanitize_text_field($table['seat'][$k] ?? '')
];
}
}
}

$new_data[] = [
'title'=>$title,
'guests'=>$guests
];

}
}

update_post_meta($post_id,'weselny_stoly',$new_data);

$search_enabled = isset($_POST['search_enabled']) ? 1 : 0;
update_post_meta($post_id,'weselny_stoly_search',$search_enabled);

$data = $new_data;
$saved = true;

}


/* =========================
   UI
========================= */

echo '<h2>Lista stołów</h2>';

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

echo '<form method="post">';

/* wyszukiwarka */
echo '<label style="display:block;margin-bottom:15px;">
<input type="checkbox" name="search_enabled" '.checked($search_enabled,1,false).'>
 Włącz wyszukiwarkę gości
</label>';

echo '<div id="tables-wrapper">';

foreach($data as $i=>$table){

echo '<div class="table-box" style="border:1px solid #ccc;padding:15px;margin-bottom:20px;">';

echo '<input type="text" name="tables['.$i.'][title]" value="'.esc_attr($table['title']).'" placeholder="Nazwa stołu"><br><br>';

echo '<div class="guest-wrapper">';

if(!empty($table['guests'])){
foreach($table['guests'] as $g){

echo '<div class="guest-row">';

echo '<input type="text" name="tables['.$i.'][guest_name][]" value="'.esc_attr($g['name']).'" placeholder="Gość">';
echo '<input type="text" name="tables['.$i.'][seat][]" value="'.esc_attr($g['seat']).'" placeholder="Miejsce">';

echo '<button type="button" class="remove-guest">X</button>';

echo '</div>';

}
}

echo '</div>';

echo '<button type="button" class="add-guest">+ Dodaj gościa</button><br><br>';
echo '<button type="button" class="remove-table">Usuń stół</button>';

echo '</div>';

}

echo '</div>';

echo '<button type="button" id="add-table">+ Dodaj stół</button><br><br>';

echo '<button name="save_all" style="font-size:16px;padding:10px 20px;">Zapisz</button>';

echo '</form>';
?>

<script>

let tableIndex = <?php echo count($data); ?>;

/* DODAJ STÓŁ */
document.getElementById("add-table").addEventListener("click", function(){

let wrapper = document.getElementById("tables-wrapper");

let div = document.createElement("div");
div.className = "table-box";
div.style = "border:1px solid #ccc;padding:15px;margin-bottom:20px;";

div.innerHTML = `
<input type="text" name="tables[${tableIndex}][title]" placeholder="Nazwa stołu"><br><br>

<div class="guest-wrapper"></div>

<button type="button" class="add-guest">+ Dodaj gościa</button><br><br>
<button type="button" class="remove-table">Usuń stół</button>
`;

wrapper.appendChild(div);

tableIndex++;

});


/* GLOBAL CLICK */
document.addEventListener("click", function(e){

/* DODAJ GOŚCIA */
if(e.target.classList.contains("add-guest")){

let table = e.target.closest(".table-box");
let wrapper = table.querySelector(".guest-wrapper");

let index = [...document.querySelectorAll(".table-box")].indexOf(table);

let row = document.createElement("div");
row.className = "guest-row";

row.innerHTML = `
<input type="text" name="tables[${index}][guest_name][]" placeholder="Gość">
<input type="text" name="tables[${index}][seat][]" placeholder="Miejsce">
<button type="button" class="remove-guest">X</button>
`;

wrapper.appendChild(row);

}

/* USUŃ GOŚCIA */
if(e.target.classList.contains("remove-guest")){
e.target.closest(".guest-row").remove();
}

/* USUŃ STÓŁ */
if(e.target.classList.contains("remove-table")){
e.target.closest(".table-box").remove();
}

});


/* KOMUNIKAT */
<?php if($saved): ?>

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

add_action('woocommerce_account_panel-wesela_endpoint','weselny_panel_stoly');