<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =========================
   RENDER MODUŁU
========================= */

function weselny_galeria_render($post_id){

$user_id = get_post_meta($post_id,'user_id',true);
if(!$user_id) return;

$enabled = get_user_meta($user_id,'weselny_modul_galeria',true);
if(!$enabled) return;


/* =========================
   WIDOK GALERII
========================= */

if(isset($_GET['galeria'])){

if(isset($_POST['upload_trigger']) && isset($_FILES['photos'])){

require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');

$files = $_FILES['photos'];

$ids = get_post_meta($post_id,'weselny_galeria',true);
if(!$ids) $ids = array();

foreach($files['name'] as $k=>$name){

if(!empty($files['tmp_name'][$k])){

$file = array(
'name'=>$files['name'][$k],
'type'=>$files['type'][$k],
'tmp_name'=>$files['tmp_name'][$k],
'error'=>$files['error'][$k],
'size'=>$files['size'][$k]
);

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

$ids[] = $id;
}
}
}

update_post_meta($post_id,'weselny_galeria',$ids);

echo "<script>alert('Zdjęcia przesłane');</script>";
}


/* UI */

echo '<p><a href="'.get_permalink().'" class="weselny-back">← Powrót</a></p>';
echo '<h2>Dodaj zdjęcia</h2>';

echo '
<form method="post" enctype="multipart/form-data" id="weselny-upload-form">

<input type="file" id="photo-input" name="photos[]" accept="image/*" capture="environment" multiple>

<div id="photo-preview" style="margin-top:15px;"></div>

<br>

<button id="upload-btn">Prześlij</button>

<div id="upload-loading" style="display:none;margin-top:10px;font-weight:bold;">
Wysyłanie<span id="dots"></span>
</div>

</form>

<script>

document.addEventListener("DOMContentLoaded", function(){

const input = document.getElementById("photo-input");
const preview = document.getElementById("photo-preview");
const form = document.getElementById("weselny-upload-form");
const loading = document.getElementById("upload-loading");
const dots = document.getElementById("dots");
const btn = document.getElementById("upload-btn");

if(!input) return;

let filesArray = [];
let dotsInterval;

function startDots(){
let count = 0;
dotsInterval = setInterval(()=>{
count = (count + 1) % 4;
dots.innerHTML = ".".repeat(count);
},500);
}

function stopDots(){
clearInterval(dotsInterval);
}

input.addEventListener("change", function(e){

const files = Array.from(e.target.files);

files.forEach(file => {

filesArray.push(file);

const reader = new FileReader();

reader.onload = function(event){

const wrapper = document.createElement("div");
wrapper.style.display = "inline-block";
wrapper.style.margin = "10px";
wrapper.style.position = "relative";

const img = document.createElement("img");
img.src = event.target.result;
img.style.width = "120px";
img.style.height = "120px";
img.style.objectFit = "cover";

const remove = document.createElement("button");
remove.innerHTML = "X";
remove.type = "button";
remove.style.position = "absolute";
remove.style.top = "0";
remove.style.right = "0";
remove.style.background = "red";
remove.style.color = "white";

remove.onclick = function(){
wrapper.remove();
filesArray = filesArray.filter(f => f !== file);
};

wrapper.appendChild(img);
wrapper.appendChild(remove);
preview.appendChild(wrapper);

};

reader.readAsDataURL(file);

});

input.value = "";

});

form.addEventListener("submit",function(e){

e.preventDefault();

if(filesArray.length === 0){
alert("Dodaj zdjęcia najpierw");
return;
}

loading.style.display = "block";
startDots();
btn.disabled = true;

const formData = new FormData();

filesArray.forEach(file => {
formData.append("photos[]", file);
});

formData.append("upload_trigger", "1");

const xhr = new XMLHttpRequest();
xhr.open("POST", window.location.href, true);

xhr.onload = function(){

stopDots();

if(xhr.status === 200){
loading.innerHTML = "Gotowe ✅";
setTimeout(()=>location.reload(),800);
}else{
loading.innerHTML = "Błąd ❌";
}

};

xhr.send(formData);

});

});

</script>
';

return;
}


/* =========================
   BLOKADA
========================= */

$active = weselny_get_active_module();

if($active && $active !== 'galeria'){
return;
}


/* =========================
   KAFEL
========================= */

$url = add_query_arg('galeria','1',get_permalink($post_id));

echo '<div class="weselny-tile">';
echo '<a href="'.esc_url($url).'">Dodaj zdjęcia</a>';
echo '</div>';

}

/* 🔥 NOWY SYSTEM */
add_action('weselny_render_module_galeria','weselny_galeria_render');