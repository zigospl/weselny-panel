<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =========================
   OPCJA WŁĄCZENIA
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
   ZAPIS WŁĄCZENIA
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


/* =========================
   USUŃ PYTANIE
========================= */

if(isset($_POST['delete_question'])){
$qi = intval($_POST['delete_question']);
unset($data[$qi]);
$data = array_values($data);
update_post_meta($post_id,'weselny_quiz',$data);
}


/* =========================
   DODAJ PYTANIE
========================= */

if(isset($_POST['dodaj_pytanie'])){

foreach($data as $i=>$q){

$data[$i]['question'] = sanitize_text_field($_POST['question'][$i] ?? '');

$answers = [];

if(isset($_POST['answer'][$i])){
foreach($_POST['answer'][$i] as $a=>$text){

if(trim($text) !== ''){
$answers[] = [
'text'=>sanitize_text_field($text),
'correct'=> isset($_POST['correct'][$i][$a]) ? 1 : 0
];
}

}
}

$data[$i]['answers'] = array_values($answers);

}

$data[] = [
'question'=>'',
'answers'=>[
['text'=>'','correct'=>0],
['text'=>'','correct'=>0]
]
];

update_post_meta($post_id,'weselny_quiz',$data);
}


/* =========================
   ZAPIS
========================= */

if(isset($_POST['zapisz_quiz'])){

foreach($data as $i=>$q){

$data[$i]['question'] = sanitize_text_field($_POST['question'][$i] ?? '');

$answers = [];

if(isset($_POST['answer'][$i])){
foreach($_POST['answer'][$i] as $a=>$text){

if(trim($text) !== ''){
$answers[] = [
'text'=>sanitize_text_field($text),
'correct'=> isset($_POST['correct'][$i][$a]) ? 1 : 0
];
}

}
}

$data[$i]['answers'] = array_values($answers);

}

update_post_meta($post_id,'weselny_quiz',$data);

echo "<script>alert('Zapisano quiz');</script>";
}


/* =========================
   HTML
========================= */

echo '<h2>Quiz</h2>';
echo '<p><a href="'.wc_get_account_endpoint_url('panel-wesela').'">← Powrót</a></p>';

echo '<form method="post">';

foreach($data as $i=>$q){

echo '<div class="quiz-question" style="position:relative;">';

echo '<button type="submit" name="delete_question" value="'.$i.'" style="
position:absolute;
top:0;
right:0;
background:red;
color:#fff;
">Usuń pytanie</button>';

echo '<h3>Pytanie '.($i+1).'</h3>';

echo '<input type="text" name="question[]" value="'.esc_attr($q['question'] ?? '').'"><br><br>';


/* 🔥 ZAWSZE MIN 2 ODPOWIEDZI */
$answers = $q['answers'] ?? [];

while(count($answers) < 2){
$answers[] = ['text'=>'','correct'=>0];
}

$answers = array_slice($answers,0,5);
$answers = array_values($answers);


foreach($answers as $a=>$ans){

$val = $ans['text'] ?? '';
$checked = !empty($ans['correct']) ? 'checked' : '';

echo '<div class="quiz-answer" style="position:relative;">';

echo '<input type="text" name="answer['.$i.']['.$a.']" value="'.esc_attr($val).'">';
echo '<label><input type="checkbox" name="correct['.$i.']['.$a.']" '.$checked.'> Poprawna</label>';

echo '<button type="button" class="remove-answer">X</button>';

echo '</div>';
}

/* ukryj jeśli 5 */
$hide = count($answers) >= 5 ? 'style="display:none;"' : '';

echo '<button type="button" class="add-answer" '.$hide.' data-q="'.$i.'">Dodaj odpowiedź</button>';

echo '<hr>';
echo '</div>';

}

echo '<button name="dodaj_pytanie">Dodaj pytanie</button><br><br>';
echo '<button name="zapisz_quiz">Zapisz</button>';

echo '</form>';


/* =========================
   JS
========================= */

echo '
<script>

document.addEventListener("click", function(e){

if(e.target.classList.contains("add-answer")){

let container = e.target.closest(".quiz-question");
let answers = container.querySelectorAll(".quiz-answer");

if(answers.length >= 5){
e.target.style.display = "none";
return;
}

let qIndex = e.target.dataset.q;
let newIndex = answers.length;

let div = document.createElement("div");
div.className = "quiz-answer";

div.innerHTML = `
<input type="text" name="answer[${qIndex}][${newIndex}]">
<label><input type="checkbox" name="correct[${qIndex}][${newIndex}]"> Poprawna</label>
<button type="button" class="remove-answer">X</button>
`;

e.target.before(div);

if(container.querySelectorAll(".quiz-answer").length >= 5){
e.target.style.display = "none";
}

}

if(e.target.classList.contains("remove-answer")){

let container = e.target.closest(".quiz-question");
let answers = container.querySelectorAll(".quiz-answer");

if(answers.length <= 2){
alert("Min 2 odpowiedzi");
return;
}

e.target.closest(".quiz-answer").remove();

let addBtn = container.querySelector(".add-answer");
if(addBtn){
addBtn.style.display = "inline-block";
}

}

});

</script>
';

}

add_action('woocommerce_account_panel-wesela_endpoint','weselny_panel_quiz');