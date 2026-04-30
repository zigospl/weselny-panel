<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function weselny_zadania_render($post_id){

$user_id = get_post_meta($post_id,'user_id',true);
if(!$user_id) return;

$enabled = get_user_meta($user_id,'weselny_modul_zadania',true);
if(!$enabled) return;


/* =========================
   UPLOAD
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

$tasks = get_post_meta($post_id,'weselny_zadania',true);
$text = get_post_meta($post_id,'weselny_zadania_text',true);
$photos = get_user_meta($user_id,'weselny_zadania_photos',true);

$random = get_post_meta($post_id,'weselny_zadania_random',true);
$count = intval(get_post_meta($post_id,'weselny_zadania_count',true));

if(!$tasks){
echo '<p><a href="'.get_permalink().'" class="weselny-back">← Powrót</a></p>';
echo '<p>Brak zadań</p>';
return;
}

$tasks = array_values($tasks);
$json = json_encode($tasks);

echo '

<style>
#zadania-app{
max-width:500px;
margin:0 auto;
font-family:Arial;
}

.z-card{
background:#fff;
border-radius:16px;
padding:25px;
box-shadow:0 10px 25px rgba(0,0,0,0.08);
margin-top:20px;
text-align:center;
}

.z-task{
font-size:22px;
font-weight:600;
margin-bottom:20px;
}

.z-btn{
display:block;
width:100%;
padding:14px;
border:none;
border-radius:10px;
font-size:16px;
cursor:pointer;
margin-top:10px;
}

.z-main{
background:#1a3d1a;
color:#fff;
}

.z-skip{
background:#aaa;
}

.z-progress{
margin-bottom:15px;
font-size:14px;
color:#666;
}

.z-bar{
height:8px;
background:#eee;
border-radius:10px;
overflow:hidden;
margin-bottom:15px;
}

.z-bar-fill{
height:100%;
background:#1a3d1a;
width:0%;
}

#preview img{
width:120px;
height:120px;
object-fit:cover;
border-radius:10px;
margin-top:10px;
}
</style>

<p><a href="'.get_permalink().'" class="weselny-back">← Powrót</a></p>

<div id="zadania-app"></div>

<script>

let baseTasks = '.$json.';

let RANDOM_MODE = '.($random ? 'true' : 'false').';
let TASK_COUNT = '.$count.';

/* DEVICE */
let device_id = localStorage.getItem("weselny_device_id");

if(!device_id){
device_id = "dev-" + Math.random().toString(36).substr(2,9);
localStorage.setItem("weselny_device_id", device_id);
}

/* STORAGE */
let storageKey = "weselny_tasks_" + device_id;

let saved = localStorage.getItem(storageKey);

let queue = [];
let completed = 0;
let skipped = 0;
let totalCount = 0;

if(saved){
try{
let parsed = JSON.parse(saved);
queue = parsed.queue || [];
completed = parsed.completed || 0;
skipped = parsed.skipped || 0;
totalCount = parsed.total || (queue.length + completed + skipped);
}catch(e){}
}

/* SHUFFLE */
function shuffle(arr){
for(let i = arr.length - 1; i > 0; i--){
const j = Math.floor(Math.random() * (i + 1));
[arr[i], arr[j]] = [arr[j], arr[i]];
}
return arr;
}

/* GENERATE */
function generateQueue(){

let base = shuffle([...baseTasks]);
let limit = baseTasks.length;

if(TASK_COUNT > 0){
limit = TASK_COUNT;
}

if(!RANDOM_MODE){
queue = [...baseTasks];
totalCount = queue.length;
return;
}

if(limit <= base.length){
queue = base.slice(0, limit);
totalCount = queue.length;
return;
}

queue = [...base];

while(queue.length < limit){
queue = queue.concat(shuffle([...baseTasks]));
}

queue = queue.slice(0, limit);
totalCount = queue.length;

}

if(queue.length === 0){
generateQueue();
}

/* SAVE */
function save(){
localStorage.setItem(storageKey, JSON.stringify({
queue: queue,
completed: completed,
skipped: skipped,
total: totalCount
}));
}

/* PROGRESS */
function progressBar(){
let percent = totalCount ? (completed / totalCount) * 100 : 0;

return `
<div class="z-progress">${completed} / ${totalCount} wykonanych</div>
<div class="z-bar"><div class="z-bar-fill" style="width:${percent}%"></div></div>
`;
}

let currentTask = null;
let selectedFile = null;

/* NEXT */
function nextTask(){

if(queue.length === 0){

document.getElementById("zadania-app").innerHTML = `
<div class="z-card">
<h2>Koniec 🎉</h2>
<p>Wykonałeś ${completed} z ${totalCount}</p>
<p>Pominięte: ${skipped}</p>
</div>
`;

localStorage.removeItem(storageKey);
return;
}

currentTask = queue.shift();
save();
renderTask();
}

function completeTask(){
completed++;
nextTask();
}

function skipTask(){
skipped++;
nextTask();
}

/* RENDER */
function renderTask(){

let html = `<div class="z-card">`;
html += progressBar();
html += `<div class="z-task">${currentTask}</div>`;
';

/* zdjęcia */

if($photos){

echo '
html += `
<input type="file" id="task-photo" accept="image/*" capture="environment">

<div id="preview"></div>

<button class="z-btn z-main" onclick="upload()">Potwierdź</button>
`;
';

}else{

echo '
html += `<button class="z-btn z-main" onclick="completeTask()">Wykonane</button>`;
';
}

echo '

html += `<button class="z-btn z-skip" onclick="skipTask()">Pomiń</button>`;
html += `</div>`;

document.getElementById("zadania-app").innerHTML = html;

let input = document.getElementById("task-photo");

if(input){
input.addEventListener("change", function(e){

selectedFile = e.target.files[0];

let reader = new FileReader();

reader.onload = function(ev){
document.getElementById("preview").innerHTML =
`<img src="${ev.target.result}">`;
};

reader.readAsDataURL(selectedFile);

});
}

}

/* UPLOAD */
function upload(){

if(!selectedFile){
alert("Dodaj zdjęcie!");
return;
}

let formData = new FormData();

formData.append("photo", selectedFile);
formData.append("task", currentTask);
formData.append("device", device_id);
formData.append("post_id", '.$post_id.');

fetch(window.location.href, {
method: "POST",
body: formData
})
.then(()=>completeTask());

}

/* START */
document.getElementById("zadania-app").innerHTML = `
<div class="z-card">
<p>'.esc_js($text).'</p>
${progressBar()}
<button class="z-btn z-main" onclick="nextTask()">Start</button>
</div>
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

add_action('weselny_render_module_zadania','weselny_zadania_render');