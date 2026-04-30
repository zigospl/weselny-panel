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
   PANEL
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
   ZAPIS
========================= */

$saved = false;

if(isset($_POST['save_all'])){

$new_data = [];

if(isset($_POST['rows'])){
foreach($_POST['rows'] as $row){

$new_data[] = [
'title' => sanitize_text_field($row['title'] ?? ''),
'time' => sanitize_text_field($row['time'] ?? ''),
'description' => wp_kses_post($row['description'] ?? '')
];

}
}

update_post_meta($post_id,'weselny_harmonogram',$new_data);
$data = $new_data;
$saved = true;

}


/* =========================
   UI
========================= */

echo '<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">';
echo '<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>';

echo '<h2>Harmonogram</h2>';
echo '<p><a href="'.wc_get_account_endpoint_url('panel-wesela').'">← Powrót</a></p>';

echo '<form method="post" id="harmonogram-form">';
echo '<div id="harmonogram-items">';

foreach($data as $i=>$row){

echo '<div class="harmonogram-item" style="border:1px solid #ccc;padding:15px;margin-bottom:20px;">';

echo '<h3>Pozycja '.($i+1).'</h3>';

echo '<input class="text-input-block" type="text" name="rows['.$i.'][title]" value="'.esc_attr($row['title']).'" placeholder="Nagłówek"><br><br>';

echo '<input class="text-input-block" type="text" name="rows['.$i.'][time]" value="'.esc_attr($row['time']).'" placeholder="Czas"><br><br>';

echo '<div class="quill-editor">'.$row['description'].'</div>';
echo '<input type="hidden" class="quill-value" name="rows['.$i.'][description]" value="'.esc_attr($row['description']).'">';

echo '<br><button type="button" class="remove-item">Usuń</button>';

echo '</div>';

}

echo '</div>';

echo '<button type="button" id="add-item">+ Dodaj pozycję</button><br><br>';
echo '<button name="save_all">Zapisz</button>';

echo '</form>';
?>

<script>

/* INIT QUILL */
function initQuill(container){
container.querySelectorAll(".quill-editor").forEach(el=>{

if(el.classList.contains("init")) return;

let hidden = el.nextElementSibling;

let q = new Quill(el,{theme:"snow"});

q.root.innerHTML = hidden.value;

q.on("text-change",()=>{
hidden.value = q.root.innerHTML;
});

el.classList.add("init");

});
}

document.addEventListener("DOMContentLoaded",()=>{
initQuill(document);
});


let index = <?php echo count($data); ?>;


/* ADD */
document.getElementById("add-item").addEventListener("click", function(){

let container = document.getElementById("harmonogram-items");

let div = document.createElement("div");
div.className = "harmonogram-item";
div.style = "border:1px solid #ccc;padding:15px;margin-bottom:20px;";

div.innerHTML = `
<h3>Pozycja ${index + 1}</h3>

<input class="text-input-block" type="text" name="rows[${index}][title]" placeholder="Nagłówek"><br><br>

<input class="text-input-block" type="text" name="rows[${index}][time]" placeholder="Czas"><br><br>

<div class="quill-editor"></div>
<input type="hidden" class="quill-value" name="rows[${index}][description]">

<br><button type="button" class="remove-item">Usuń</button>
`;

container.appendChild(div);

initQuill(div);

index++;

});


/* REMOVE */
document.addEventListener("click", function(e){

if(e.target.classList.contains("remove-item")){
e.target.closest(".harmonogram-item").remove();
}

});

</script>

<?php

}

add_action('woocommerce_account_panel-wesela_endpoint','weselny_panel_harmonogram');