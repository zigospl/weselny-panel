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
Włącz
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

echo '<div style="border:1px solid #ccc;padding:20px;display:inline-block;margin:10px;">';
echo '<a href="'.wc_get_account_endpoint_url('panel-wesela').'?quiz=1">Quiz</a>';
echo '</div>';

}

}

add_action('weselny_panel_tiles','weselny_quiz_tile');


/* =========================
   PANEL KLIENTA
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

/* ustawienia */
$settings = get_post_meta($post_id,'weselny_quiz_settings',true);
if(!$settings) $settings = ['random'=>0,'limit'=>0];


/* =========================
   ZAPIS USTAWIEŃ
========================= */

if(isset($_POST['zapisz_settings'])){

$settings['random'] = isset($_POST['random']) ? 1 : 0;
$settings['limit'] = intval($_POST['limit']);

update_post_meta($post_id,'weselny_quiz_settings',$settings);

}


/* =========================
   DODAJ PYTANIE
========================= */

if(isset($_POST['dodaj_pytanie'])){

/* zapisz aktualne dane */

if(isset($_POST['question'])){
foreach($data as $i=>$q){

$data[$i]['question'] = sanitize_text_field($_POST['question'][$i]);

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

$data[$i]['answers'] = $answers;

}
}

/* dodaj nowe pytanie z 2 odpowiedziami */

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
   ZAPIS QUIZU
========================= */

if(isset($_POST['zapisz_quiz'])){

foreach($data as $i=>$q){

$data[$i]['question'] = sanitize_text_field($_POST['question'][$i]);

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

$data[$i]['answers'] = $answers;

}

update_post_meta($post_id,'weselny_quiz',$data);

echo "<script>alert('Zapisano quiz');</script>";

}


/* =========================
   HTML
========================= */

echo '<h2>Quiz</h2>';
echo '<p><a href="'.wc_get_account_endpoint_url('panel-wesela').'">← Powrót</a></p>';


/* ustawienia */

echo '<form method="post">';

echo '<h3>Ustawienia</h3>';

echo '<label><input type="checkbox" name="random" '.checked($settings['random'],1,false).'> Losowa kolejność</label><br><br>';
echo '<input type="number" name="limit" placeholder="Limit pytań (0 = wszystkie)" value="'.$settings['limit'].'"><br><br>';

echo '<button name="zapisz_settings">Zapisz ustawienia</button>';

echo '</form><hr>';


/* pytania */

echo '<form method="post">';

foreach($data as $i=>$q){

echo '<div class="quiz-question">';

echo '<h3>Pytanie '.($i+1).'</h3>';

echo '<input type="text" name="question[]" value="'.esc_attr($q['question']).'" placeholder="Pytanie"><br><br>';

$answers = $q['answers'] ?? [];

foreach($answers as $a=>$ans){

$val = $ans['text'] ?? '';
$checked = !empty($ans['correct']) ? 'checked' : '';

echo '<div class="quiz-answer">';
echo '<input type="text" name="answer['.$i.']['.$a.']" value="'.esc_attr($val).'" placeholder="Odpowiedź">';
echo '<label><input type="checkbox" name="correct['.$i.']['.$a.']" '.$checked.'> Poprawna</label>';
echo '</div>';

}

echo '<br><button type="button" class="add-answer" data-q="'.$i.'">Dodaj odpowiedź</button>';

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

let qIndex = e.target.dataset.q;
let container = e.target.closest(".quiz-question");

let answers = container.querySelectorAll(".quiz-answer");

if(answers.length >= 5){
alert("Maksymalnie 5 odpowiedzi");
return;
}

let newIndex = answers.length;

let div = document.createElement("div");
div.className = "quiz-answer";

div.innerHTML = `
<input type="text" name="answer[${qIndex}][${newIndex}]" placeholder="Odpowiedź">
<label><input type="checkbox" name="correct[${qIndex}][${newIndex}]"> Poprawna</label>
`;

e.target.before(div);

}

});

</script>
';

}

add_action('woocommerce_account_panel-wesela_endpoint','weselny_panel_quiz');