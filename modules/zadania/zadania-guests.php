<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =========================
   RENDER MODUŁU
========================= */

function weselny_zadania_render($post_id){

$user_id = get_post_meta($post_id,'user_id',true);
if(!$user_id) return;

$enabled = get_user_meta($user_id,'weselny_modul_zadania',true);
if(!$enabled) return;


/* =========================
   UPLOAD (AJAX bez ajaxa 😄)
========================= */

if(isset($_FILES['photo'])){

require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');

$file = $_FILES['photo'];

$upload = wp_handle_upload($file,['test_form'=>false]);

if(!isset($upload['error'])){

$attachment = [
'post_mime_type'=>$upload['type'],
'post_title'=>sanitize_file_name($upload['file']),
'post_status'=>'inherit'
];

$id = wp_insert_attachment($attachment,$upload['file']);

$meta = wp_generate_attachment_metadata($id,$upload['file']);
wp_update_attachment_metadata($id,$meta);

$data = get_post_meta($_POST['post_id'],'weselny_zadania_photos_data',true);
if(!$data) $data = [];

$device = sanitize_text_field($_POST['device']);
$task = sanitize_text_field($_POST['task']);

$data[$device][] = [
'id'=>$id,
'task'=>$task
];

update_post_meta($_POST['post_id'],'weselny_zadania_photos_data',$data);

}

exit;
}


/* =========================
   WIDOK
========================= */

if(isset($_GET['zadania'])){

$data = get_post_meta($post_id,'weselny_zadania',true);
$text = get_post_meta($post_id,'weselny_zadania_text',true);
$photos = get_user_meta($user_id,'weselny_zadania_photos',true);

if(!$data){
echo '<p><a href="'.get_permalink().'" class="weselny-back">← Powrót</a></p>';
echo '<p>Brak zadań</p>';
return;
}

$json = json_encode(array_values($data));

echo '
<p><a href="'.get_permalink().'" class="weselny-back">← Powrót</a></p>

<div id="zadania-app"></div>

<script>

let tasks = '.$json.';
let device_id = localStorage.getItem("weselny_device_id");

if(!device_id){
device_id = "dev-" + Math.random().toString(36).substr(2,9);
localStorage.setItem("weselny_device_id", device_id);
}

/* STORAGE */

let storageKey = "weselny_tasks_" + device_id;

let saved = localStorage.getItem(storageKey);

let used = [];
let completed = 0;

if(saved){
    try{
        let parsed = JSON.parse(saved);
        used = parsed.used || [];
        completed = parsed.completed || 0;
    }catch(e){}
}

function saveProgress(){
    localStorage.setItem(storageKey, JSON.stringify({
        used: used,
        completed: completed
    }));
}

let currentTask = null;
let selectedFile = null;

function progress(){
return `<p><strong>${completed} / ${tasks.length} wykonanych zadań</strong></p>`;
}

/* LOSOWANIE */

function losuj(){

if(used.length >= tasks.length){

document.getElementById("zadania-app").innerHTML = `
<h2>Gratulacje! 🎉</h2>
<p>Wykonałeś <strong>${completed} / ${tasks.length}</strong> zadań</p>
`;

localStorage.removeItem(storageKey);
return;
}

let index;

do{
index = Math.floor(Math.random()*tasks.length);
}while(used.includes(index));

used.push(index);
saveProgress();

currentTask = tasks[index];

let html = progress();
html += "<h2>"+currentTask+"</h2>";
';

/* TRYB ZDJĘĆ */

if($photos){

echo '
html += `
<input type="file" id="task-photo" accept="image/*" capture="environment"><br><br>

<div id="preview"></div>

<div id="loading" style="display:none;font-weight:bold;margin-top:10px;">
Wysyłanie<span id="dots"></span>
</div>

<br>

<button onclick="upload()">Potwierdź wykonanie</button>
`;
';

}else{

echo '
html += `<button onclick="next()">Potwierdź wykonanie</button>`;
';
}

echo '

html += "<br><br><button onclick=\'losuj()\'>Zrezygnuj</button>";

document.getElementById("zadania-app").innerHTML = html;

let input = document.getElementById("task-photo");

if(input){
input.addEventListener("change", function(e){

selectedFile = e.target.files[0];

let reader = new FileReader();

reader.onload = function(ev){

document.getElementById("preview").innerHTML = `
<div style="position:relative;display:inline-block;">
<img src="${ev.target.result}" style="width:120px;height:120px;object-fit:cover;border:1px solid #ccc;">
<button onclick="removePhoto()" style="
position:absolute;
top:0;
right:0;
background:red;
color:white;
border:none;
cursor:pointer;
">X</button>
</div>
`;

};

reader.readAsDataURL(selectedFile);

});
}

}

/* REMOVE PHOTO */

function removePhoto(){
selectedFile = null;
document.getElementById("preview").innerHTML = "";
}

/* NEXT */

function next(){
completed++;
saveProgress();
losuj();
}

/* DOTS */

let dotsInterval;

function startDots(){
let count = 0;
dotsInterval = setInterval(()=>{
count = (count + 1) % 4;
document.getElementById("dots").innerHTML = ".".repeat(count);
},500);
}

function stopDots(){
clearInterval(dotsInterval);
}

/* UPLOAD */

function upload(){

if(!selectedFile){
alert("Dodaj zdjęcie!");
return;
}

let loading = document.getElementById("loading");

loading.style.display = "block";
startDots();

let formData = new FormData();

formData.append("photo", selectedFile);
formData.append("task", currentTask);
formData.append("device", device_id);
formData.append("post_id", '.$post_id.');

fetch(window.location.href, {
method: "POST",
body: formData
})
.then(r => r.text())
.then(() => {

completed++;
saveProgress();

stopDots();
loading.innerHTML = "Gotowe ✅";

setTimeout(()=>{
losuj();
},800);

});

}

/* START */

document.getElementById("zadania-app").innerHTML = `
<p>'.esc_js($text).'</p>
${progress()}
<button onclick="losuj()">Losuj zadanie</button>
`;

</script>
';

return;
}


/* =========================
   BLOKADA
========================= */

$active = weselny_get_active_module();

if($active && $active !== 'zadania'){
return;
}


/* =========================
   KAFEL
========================= */

$url = add_query_arg('zadania','1',get_permalink($post_id));

echo '<div class="weselny-tile">';
echo '<a href="'.esc_url($url).'">Zadania</a>';
echo '</div>';

}

/* 🔥 NOWY SYSTEM */
add_action('weselny_render_module_zadania','weselny_zadania_render');