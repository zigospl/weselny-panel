<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function weselny_quiz_guest($content){

if(get_post_type()!=='wesela' || !is_singular('wesela')) return $content;

$post_id = get_the_ID();

$user_id = get_post_meta($post_id,'user_id',true);
if(!$user_id) return $content;

$enabled = get_user_meta($user_id,'weselny_modul_quiz',true);
if(!$enabled) return $content;


/* =========================
   WIDOK QUIZU
========================= */

if(isset($_GET['quiz'])){

$data = get_post_meta($post_id,'weselny_quiz',true);
$settings = get_post_meta($post_id,'weselny_quiz_settings',true);

if(!$data) return '<p>Brak quizu</p>';

/* losowość */
if(!empty($settings['random'])){
shuffle($data);
}

/* limit */
if(!empty($settings['limit'])){
$data = array_slice($data,0,$settings['limit']);
}

$json = json_encode($data);

$html = '
<p><a href="'.get_permalink().'">← Powrót</a></p>

<div id="quiz-app"></div>

<script>

let questions = '.$json.';
let index = 0;
let score = 0;

function render(){

let q = questions[index];

let html = "<h2>"+q.question+"</h2>";

q.answers.forEach((a,i)=>{
html += `<label><input type="radio" name="ans" value="${i}"> ${a.text}</label><br>`;
});

html += "<br><button onclick=\'next()\'>Wybierz odpowiedź</button>";

document.getElementById("quiz-app").innerHTML = html;

}

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

if(index >= questions.length){

document.getElementById("quiz-app").innerHTML =
"<h2>Gratulacje!</h2><p>Poprawne odpowiedzi: "+score+"/"+questions.length+"</p>";

return;
}

render();

}

render();

</script>
';

return $html;
}


/* =========================
   BLOKADA
========================= */

$active = weselny_get_active_module();

if($active && $active !== 'stoly'){
    return $content;
}

/* =========================
   KAFEL
========================= */

$url = add_query_arg('quiz','1',get_permalink());

$html = '<div style="border:1px solid #ccc;padding:20px;display:inline-block;margin:10px;">';
$html .= '<a href="'.$url.'">Quiz</a>';
$html .= '</div>';

return $content.$html;

}

add_filter('the_content','weselny_quiz_guest');