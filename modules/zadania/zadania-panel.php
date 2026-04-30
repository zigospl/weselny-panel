<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =========================
   OPCJA WŁĄCZENIA
========================= */

function weselny_zadania_option(){

$enabled = get_user_meta(get_current_user_id(),'weselny_modul_zadania',true);
?>

<p><strong>Zadania dla gości</strong></p>

<label>
<input type="checkbox" name="zadania_enabled" <?php checked($enabled,1); ?>>
</label>

<hr>

<?php
}
add_action('weselny_panel_features','weselny_zadania_option');


/* =========================
   ZAPIS OPCJI
========================= */

function weselny_zadania_save(){

if(isset($_POST['weselny_features_save'])){
$enabled = isset($_POST['zadania_enabled']) ? 1 : 0;
update_user_meta(get_current_user_id(),'weselny_modul_zadania',$enabled);
}

}
add_action('init','weselny_zadania_save');


/* =========================
   KAFEL
========================= */

function weselny_zadania_tile(){

$enabled = get_user_meta(get_current_user_id(),'weselny_modul_zadania',true);

if(!$enabled) return;

echo '<div class="weselny-tile">';
echo '<a href="'.wc_get_account_endpoint_url('panel-wesela').'?zadania=1">Zadania</a>';
echo '</div>';

}
add_action('weselny_panel_tiles','weselny_zadania_tile');


/* =========================
   PANEL
========================= */

function weselny_panel_zadania(){

if(!isset($_GET['zadania'])) return;

$user_id = get_current_user_id();

$wedding = get_posts([
'post_type'=>'wesela',
'meta_key'=>'user_id',
'meta_value'=>$user_id,
'posts_per_page'=>1
]);

if(!$wedding) return;

$post_id = $wedding[0]->ID;


/* =========================
   DANE
========================= */

$data = get_post_meta($post_id,'weselny_zadania',true);
if(!$data) $data = [];

$text = get_post_meta($post_id,'weselny_zadania_text',true);

$photos = get_user_meta($user_id,'weselny_zadania_photos',true);
$random = get_post_meta($post_id,'weselny_zadania_random',true);
$count = intval(get_post_meta($post_id,'weselny_zadania_count',true));


/* =========================
   SAVE
========================= */

if(isset($_POST['save_all'])){

$text = wp_kses_post($_POST['text'] ?? '');
update_post_meta($post_id,'weselny_zadania_text',$text);

$photos = isset($_POST['photos']) ? 1 : 0;
update_user_meta($user_id,'weselny_zadania_photos',$photos);

$random = isset($_POST['random']) ? 1 : 0;
$count = intval($_POST['count'] ?? 0);

update_post_meta($post_id,'weselny_zadania_random',$random);
update_post_meta($post_id,'weselny_zadania_count',$count);

$new_tasks = [];

if(isset($_POST['task'])){
foreach($_POST['task'] as $t){
if(trim($t)!=''){
$new_tasks[] = sanitize_text_field($t);
}
}
}

update_post_meta($post_id,'weselny_zadania',$new_tasks);
$data = $new_tasks;

}


/* =========================
   UI
========================= */

echo '
<style>
.tasks-wrapper{
display:flex;
flex-direction:column;
gap:10px;
margin-top:10px;
}

.task-item{
display:flex;
align-items:center;
gap:10px;
background:#fff;
padding:12px;
border-radius:10px;
border:1px solid #ddd;
box-shadow:0 2px 6px rgba(0,0,0,0.05);
}

.task-item input{
flex:1;
padding:10px;
border-radius:6px;
border:1px solid #ccc;
}

.remove-task{
background:#e74c3c;
color:#fff;
border:none;
border-radius:6px;
padding:8px;
cursor:pointer;
}

.add-task-btn{
background:#1a3d1a;
color:#fff;
padding:10px 15px;
border:none;
border-radius:6px;
cursor:pointer;
margin-top:10px;
}

.save-btn{
background:#000;
color:#fff;
padding:12px 20px;
border:none;
border-radius:6px;
cursor:pointer;
font-size:16px;
}
</style>
';

echo '<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">';
echo '<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>';

echo '<h2>Zadania</h2>';
echo '<p><a href="'.wc_get_account_endpoint_url('panel-wesela').'">← Powrót</a></p>';

echo '<form method="post">';

/* QUILL */
echo '<label><strong>Instrukcja dla gości / tekst powitalny</strong></label><br><br>';
echo '<div class="quill-editor">'.$text.'</div>';
echo '<input type="hidden" name="text" class="quill-value" value="'.esc_attr($text).'">';

echo '<br><br>';

/* OPCJE */
echo '<label><input type="checkbox" name="photos" '.checked($photos,1,false).'> Wymagaj zdjęcia</label><br><br>';

echo '<label><input type="checkbox" id="random-toggle" name="random" '.checked($random,1,false).'> Losowe zadania</label><br><br>';

echo '<div id="random-settings" style="display:'.($random?'block':'none').'">
Ilość zadań (0 = wszystkie): 
<input type="number" name="count" value="'.esc_attr($count).'" min="0">
</div><br>';

/* TASKI */

echo '<div class="tasks-wrapper" id="tasks-wrapper">';

foreach($data as $t){
echo '
<div class="task-item">
<input type="text" name="task[]" value="'.esc_attr($t).'" placeholder="Zadanie">
<button type="button" class="remove-task">✕</button>
</div>
';
}

echo '</div>';

echo '<button type="button" id="add-task" class="add-task-btn">+ Dodaj zadanie</button><br><br>';

echo '<button name="save_all" class="save-btn">Zapisz</button>';

echo '</form>';
?>

<script>

/* QUILL */
document.addEventListener("DOMContentLoaded",function(){

let q = new Quill(document.querySelector(".quill-editor"),{theme:"snow"});
let hidden = document.querySelector(".quill-value");

q.root.innerHTML = hidden.value;

q.on("text-change",()=>{
hidden.value = q.root.innerHTML;
});

});


/* ADD TASK */
document.getElementById("add-task").onclick = function(){

let div = document.createElement("div");
div.className = "task-item";

div.innerHTML = `
<input type="text" name="task[]" placeholder="Zadanie">
<button type="button" class="remove-task">✕</button>
`;

document.getElementById("tasks-wrapper").appendChild(div);

};


/* REMOVE */
document.addEventListener("click",function(e){
if(e.target.classList.contains("remove-task")){
e.target.closest(".task-item").remove();
}
});


/* RANDOM TOGGLE */
document.getElementById("random-toggle").addEventListener("change",function(){
document.getElementById("random-settings").style.display = this.checked ? "block" : "none";
});

</script>

<?php

}

add_action('woocommerce_account_panel-wesela_endpoint','weselny_panel_zadania');