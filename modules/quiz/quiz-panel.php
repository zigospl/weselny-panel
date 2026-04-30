<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =========================
   OPCJA
========================= */

function weselny_quiz_option(){

$enabled = get_user_meta(get_current_user_id(),'weselny_modul_quiz',true);
?>

<p><strong>Quiz</strong></p>

<label>
<input type="checkbox" name="quiz_enabled" <?php checked($enabled,1); ?>>
</label>

<hr>

<?php
}
add_action('weselny_panel_features','weselny_quiz_option');


/* =========================
   ZAPIS OPCJI
========================= */

function weselny_quiz_save(){

if(isset($_POST['weselny_features_save'])){
$enabled = isset($_POST['quiz_enabled']) ? 1 : 0;
update_user_meta(get_current_user_id(),'weselny_modul_quiz',$enabled);
}

}
add_action('init','weselny_quiz_save');


/* =========================
   KAFEL
========================= */

function weselny_quiz_tile(){

$enabled = get_user_meta(get_current_user_id(),'weselny_modul_quiz',true);

if($enabled){
echo '<div class="weselny-tile">';
echo '<a href="'.wc_get_account_endpoint_url('panel-wesela').'?quiz=1">Quiz</a>';
echo '</div>';
}

}
add_action('weselny_panel_tiles','weselny_quiz_tile');


/* =========================
   PANEL
========================= */

function weselny_panel_quiz(){

if(!isset($_GET['quiz'])) return;

$user_id = get_current_user_id();

$wedding = get_posts([
'post_type'=>'wesela',
'meta_key'=>'user_id',
'meta_value'=>$user_id,
'posts_per_page'=>1
]);

if(!$wedding) return;

$post_id = $wedding[0]->ID;

$data = get_post_meta($post_id,'weselny_quiz',true);
if(!$data) $data = [];

$settings = get_post_meta($post_id,'weselny_quiz_settings',true);
if(!is_array($settings)) $settings = [];

$random = $settings['random'] ?? 0;
$count  = $settings['limit'] ?? 0;


/* =========================
   HELPER
========================= */

function weselny_collect_quiz($post){

$new = [];

if(isset($post['questions'])){
foreach($post['questions'] as $q){

$question = sanitize_text_field($q['question'] ?? '');

$answers = [];

if(isset($q['answers'])){
foreach($q['answers'] as $a){

if(trim($a['text']) !== ''){
$answers[] = [
'text'=>sanitize_text_field($a['text']),
'correct'=> isset($a['correct']) ? 1 : 0
];
}
}
}

$new[] = [
'question'=>$question,
'answers'=>$answers
];

}
}

return $new;
}


/* =========================
   ZAPIS
========================= */

$saved = false;

if(isset($_POST['save_all'])){

$data = weselny_collect_quiz($_POST);
update_post_meta($post_id,'weselny_quiz',$data);

/* 🔥 ustawienia */
$random = isset($_POST['random']) ? 1 : 0;
$count  = intval($_POST['count'] ?? 0);

update_post_meta($post_id,'weselny_quiz_settings',[
'random'=>$random,
'limit'=>$count
]);

$saved = true;

}


/* =========================
   UI
========================= */

echo '<h2>Quiz</h2>';
echo '<p><a href="'.wc_get_account_endpoint_url('panel-wesela').'">← Powrót</a></p>';

/* komunikat */
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

/* 🔥 LOSOWOŚĆ */
echo '<label><input type="checkbox" id="random-toggle" name="random" '.checked($random,1,false).'> Losowe pytania</label><br><br>';

echo '<div id="random-settings" style="display:'.($random ? 'block' : 'none').';margin-bottom:15px;">
Ilość pytań (0 = wszystkie):
<input type="number" name="count" value="'.esc_attr($count).'" min="0" style="width:80px;">
</div>';

echo '<div id="quiz-wrapper">';

foreach($data as $i=>$q){

echo '<div class="quiz-question" style="border:1px solid #ccc;padding:15px;margin-bottom:20px;">';

echo '<button type="button" class="remove-question">Usuń pytanie</button>';

echo '<h3>Pytanie</h3>';

echo '<input type="text" name="questions['.$i.'][question]" value="'.esc_attr($q['question']).'"><br><br>';

$answers = $q['answers'] ?? [];

while(count($answers) < 2){
$answers[] = ['text'=>'','correct'=>0];
}

$answers = array_slice($answers,0,5);

foreach($answers as $a=>$ans){

echo '<div class="quiz-answer">';

echo '<input type="text" name="questions['.$i.'][answers]['.$a.'][text]" value="'.esc_attr($ans['text']).'">';
echo '<label><input type="checkbox" name="questions['.$i.'][answers]['.$a.'][correct]" '.checked($ans['correct'],1,false).'> Poprawna</label>';

echo '<button type="button" class="remove-answer">X</button>';

echo '</div>';
}

echo '<button type="button" class="add-answer">+ Dodaj odpowiedź</button>';

echo '</div>';
}

echo '</div>';

echo '<button type="button" id="add-question">+ Dodaj pytanie</button><br><br>';

echo '<button name="save_all" style="font-size:16px;padding:10px 20px;">Zapisz</button>';

echo '</form>';
?>

<script>

let questionIndex = <?php echo count($data); ?>;

/* toggle losowości */
document.getElementById("random-toggle").addEventListener("change",function(){
document.getElementById("random-settings").style.display = this.checked ? "block" : "none";
});

/* dodaj pytanie */
document.getElementById("add-question").addEventListener("click", function(){

let wrapper = document.getElementById("quiz-wrapper");

let div = document.createElement("div");
div.className = "quiz-question";
div.style = "border:1px solid #ccc;padding:15px;margin-bottom:20px;";

div.innerHTML = `
<button type="button" class="remove-question">Usuń pytanie</button>

<h3>Pytanie</h3>

<input type="text" name="questions[${questionIndex}][question]"><br><br>

<div class="quiz-answer">
<input type="text" name="questions[${questionIndex}][answers][0][text]">
<label><input type="checkbox" name="questions[${questionIndex}][answers][0][correct]"> Poprawna</label>
<button type="button" class="remove-answer">X</button>
</div>

<div class="quiz-answer">
<input type="text" name="questions[${questionIndex}][answers][1][text]">
<label><input type="checkbox" name="questions[${questionIndex}][answers][1][correct]"> Poprawna</label>
<button type="button" class="remove-answer">X</button>
</div>

<button type="button" class="add-answer">+ Dodaj odpowiedź</button>
`;

wrapper.appendChild(div);
questionIndex++;

});


/* global */
document.addEventListener("click", function(e){

if(e.target.classList.contains("remove-question")){
e.target.closest(".quiz-question").remove();
}

if(e.target.classList.contains("add-answer")){

let container = e.target.closest(".quiz-question");
let answers = container.querySelectorAll(".quiz-answer");

if(answers.length >= 5) return;

let qIndex = [...document.querySelectorAll(".quiz-question")].indexOf(container);
let newIndex = answers.length;

let div = document.createElement("div");
div.className = "quiz-answer";

div.innerHTML = `
<input type="text" name="questions[${qIndex}][answers][${newIndex}][text]">
<label><input type="checkbox" name="questions[${qIndex}][answers][${newIndex}][correct]"> Poprawna</label>
<button type="button" class="remove-answer">X</button>
`;

e.target.before(div);

}

if(e.target.classList.contains("remove-answer")){

let container = e.target.closest(".quiz-question");
let answers = container.querySelectorAll(".quiz-answer");

if(answers.length <= 2){
alert("Min 2 odpowiedzi");
return;
}

e.target.closest(".quiz-answer").remove();

}

});


/* komunikat */
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

add_action('woocommerce_account_panel-wesela_endpoint','weselny_panel_quiz');