<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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


/* =========================
   FILTER
========================= */

$clean = [];

foreach($data as $q){

$question = trim($q['question'] ?? '');
if($question === '') continue;

$answers = [];

if(!empty($q['answers'])){
foreach($q['answers'] as $a){

$text = trim($a['text'] ?? '');

if($text !== ''){
$answers[] = [
'text'=>$text,
'correct'=> !empty($a['correct'])
];
}
}
}

if(count($answers) < 2) continue;

$clean[] = [
'question'=>$question,
'answers'=>$answers
];
}

if(empty($clean)){
echo '<p>Brak poprawnych pytań</p>';
return;
}


/* =========================
   RANDOM + LIMIT
========================= */

if(!empty($settings['random'])){
shuffle($clean);
}

if(!empty($settings['limit'])){
$clean = array_slice($clean,0,$settings['limit']);
}

$json = json_encode(array_values($clean));


echo '

<style>
#quiz-app{
max-width:500px;
margin:0 auto;
font-family:Arial;
}

.q-card{
background:#fff;
border-radius:16px;
padding:25px;
box-shadow:0 10px 25px rgba(0,0,0,0.08);
margin-top:20px;
}

.q-question{
font-size:20px;
font-weight:600;
margin-bottom:20px;
}

.q-answer{
display:block;
padding:12px;
border-radius:10px;
border:1px solid #ddd;
margin-bottom:10px;
cursor:pointer;
transition:0.2s;
}

.q-answer:hover{
border-color:#1a3d1a;
background:#f9f9f9;
}

.q-answer input{
margin-right:8px;
}

.q-btn{
display:block;
width:100%;
padding:14px;
border:none;
border-radius:10px;
font-size:16px;
cursor:pointer;
margin-top:10px;
}

.q-main{
background:#1a3d1a;
color:#fff;
}

.q-progress{
font-size:14px;
color:#666;
margin-bottom:10px;
}

.q-bar{
height:8px;
background:#eee;
border-radius:10px;
overflow:hidden;
margin-bottom:15px;
}

.q-bar-fill{
height:100%;
background:#1a3d1a;
width:0%;
}

.q-center{
text-align:center;
}
</style>

<p><a href="'.get_permalink().'" class="weselny-back">← Powrót</a></p>

<div id="quiz-app"></div>

<script>

let questions = '.$json.';
let total = questions.length;

/* DEVICE */
let device_id = localStorage.getItem("weselny_device_id");

if(!device_id){
device_id = "dev-" + Math.random().toString(36).substr(2,9);
localStorage.setItem("weselny_device_id", device_id);
}

/* STORAGE */
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

/* SAVE */
function save(){
localStorage.setItem(storageKey, JSON.stringify({
index:index,
score:score
}));
}

/* PROGRESS BAR */
function progressBar(){
let percent = total ? (index / total) * 100 : 0;

return `
<div class="q-progress">${score} / ${total} poprawnych</div>
<div class="q-bar"><div class="q-bar-fill" style="width:${percent}%"></div></div>
`;
}

/* RENDER */
function render(){

let q = questions[index];

let html = `<div class="q-card">`;

html += progressBar();
html += `<div class="q-question">${q.question}</div>`;

q.answers.forEach((a,i)=>{
html += `
<label class="q-answer">
<input type="radio" name="ans" value="${i}">
${a.text}
</label>
`;
});

html += `<button class="q-btn q-main" onclick="next()">Dalej</button>`;
html += `</div>`;

document.getElementById("quiz-app").innerHTML = html;
}

/* NEXT */
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
save();

if(index >= total){

document.getElementById("quiz-app").innerHTML = `
<div class="q-card q-center">
<h2>Koniec 🎉</h2>
<p>Twój wynik:</p>
<p><strong>${score} / ${total}</strong></p>
<button class="q-btn q-main" onclick="restartQuiz()">Zagraj ponownie</button>
</div>
`;

return;
}

render();
}

/* RESTART */
function restartQuiz(){
localStorage.removeItem(storageKey);
index = 0;
score = 0;
render();
}

/* START */
if(index >= total){

document.getElementById("quiz-app").innerHTML = `
<div class="q-card q-center">
<h2>Koniec 🎉</h2>
<p><strong>${score} / ${total}</strong></p>
<button class="q-btn q-main" onclick="restartQuiz()">Zagraj ponownie</button>
</div>
`;

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

add_action('weselny_render_module_quiz','weselny_quiz_render');