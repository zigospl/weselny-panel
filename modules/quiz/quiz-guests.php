<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =========================
   RENDER MODUŁU
========================= */

function weselny_quiz_render($post_id){

$user_id = get_post_meta($post_id,'user_id',true);
if(!$user_id) return;

$enabled = get_user_meta($user_id,'weselny_modul_quiz',true);
if(!$enabled) return;


/* =========================
   WIDOK QUIZU
========================= */

if(isset($_GET['quiz'])){

$data = get_post_meta($post_id,'weselny_quiz',true);
$settings = get_post_meta($post_id,'weselny_quiz_settings',true);

if(!$data){
echo '<p>Brak quizu</p>';
return;
}

/* losowość */
if(!empty($settings['random'])){
shuffle($data);
}

/* limit */
if(!empty($settings['limit'])){
$data = array_slice($data,0,$settings['limit']);
}

$json = json_encode($data);

echo '
<p><a href="'.get_permalink().'" class="weselny-back">← Powrót</a></p>

<div id="quiz-app"></div>

<script>

let questions = '.$json.';

/* =========================
   DEVICE + STORAGE
========================= */

let device_id = localStorage.getItem("weselny_device_id");

if(!device_id){
device_id = "dev-" + Math.random().toString(36).substr(2,9);
localStorage.setItem("weselny_device_id", device_id);
}

let storageKey = "weselny_quiz_" + device_id;

let saved = localStorage.getItem(storageKey);

let index = 0;
let score = 0;

if(saved){
    try{
        let parsed = JSON.parse(saved);
        index = parsed.index || 0;
        score = parsed.score || 0;
    }catch(e){}
}

function saveProgress(){
    localStorage.setItem(storageKey, JSON.stringify({
        index: index,
        score: score
    }));
}


/* =========================
   RENDER
========================= */

function render(){

let q = questions[index];

let html = `<p><strong>Pytanie ${index+1} / ${questions.length}</strong></p>`;
html += "<h2>"+q.question+"</h2>";

q.answers.forEach((a,i)=>{
html += `<label><input type="radio" name="ans" value="${i}"> ${a.text}</label><br>`;
});

html += "<br><button onclick=\'next()\'>Wybierz odpowiedź</button>";

document.getElementById("quiz-app").innerHTML = html;

}


/* =========================
   NEXT
========================= */

function next(){

let selected = document.querySelector("input[name=ans]:checked");

if(!selected){
alert("Wybierz odpowiedź");
return;
}

let val = selected.value;

if(questions[index].answers[val].correct){
score++;
}

index++;
saveProgress();

if(index >= questions.length){

document.getElementById("quiz-app").innerHTML =
`<h2>Gratulacje! 🎉</h2>
<p>Poprawne odpowiedzi: <strong>${score}/${questions.length}</strong></p>

<br>

<button onclick="restartQuiz()">Spróbuj ponownie</button>`;

return;
}

render();

}


/* =========================
   RESTART
========================= */

function restartQuiz(){

localStorage.removeItem(storageKey);

index = 0;
score = 0;

render();

}


/* =========================
   START
========================= */

if(index >= questions.length){

document.getElementById("quiz-app").innerHTML =
`<h2>Gratulacje! 🎉</h2>
<p>Poprawne odpowiedzi: <strong>${score}/${questions.length}</strong></p>

<br>

<button onclick="restartQuiz()">Spróbuj ponownie</button>`;

}else{
render();
}

</script>
';

return;
}


/* =========================
   BLOKADA
========================= */

$active = weselny_get_active_module();

if($active && $active !== 'quiz'){
return;
}


/* =========================
   KAFEL
========================= */

$url = add_query_arg('quiz','1',get_permalink($post_id));

echo '<div class="weselny-tile">';
echo '<a href="'.esc_url($url).'">Quiz</a>';
echo '</div>';

}

/* 🔥 NOWY SYSTEM */
add_action('weselny_render_module_quiz','weselny_quiz_render');