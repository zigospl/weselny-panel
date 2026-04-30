<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function weselny_stoly_render($post_id){

$user_id = get_post_meta($post_id,'user_id',true);
if(!$user_id) return;

$enabled = get_user_meta($user_id,'weselny_modul_stoly',true);
if(!$enabled) return;

$data = get_post_meta($post_id,'weselny_stoly',true);
if(!$data) return;

$search_enabled = get_post_meta($post_id,'weselny_stoly_search',true);


/* =========================
   WIDOK
========================= */

if(isset($_GET['stoly'])){

echo '
<style>

.stoly-container{
max-width:600px;
margin:0 auto;
font-family:Arial;
}

/* SEARCH */
.stoly-search{
position:sticky;
top:0;
background:#fff;
padding-bottom:10px;
z-index:10;
}

.stoly-search input{
width:100%;
padding:12px;
border-radius:10px;
border:1px solid #ddd;
font-size:14px;
}

/* TABLE CARD */
.stoly-card{
background:#fff;
border-radius:14px;
box-shadow:0 4px 12px rgba(0,0,0,0.08);
margin-bottom:15px;
overflow:hidden;
}

/* HEADER */
.stoly-header{
padding:14px;
font-weight:600;
font-size:16px;
border-bottom:1px solid #eee;
background:#fafafa;
}

/* GUEST */
.guest-item{
display:flex;
justify-content:space-between;
align-items:center;
padding:12px 14px;
border-bottom:1px solid #f1f1f1;
font-size:14px;
}

.guest-item:last-child{
border-bottom:none;
}

.guest-name{
font-weight:500;
}

.guest-seat{
font-size:12px;
color:#666;
background:#f2f2f2;
padding:4px 8px;
border-radius:6px;
}

/* HIGHLIGHT */
.highlight{
background:#ffe58a;
}

</style>
';

echo '<div class="stoly-container">';

echo '<p><a href="'.get_permalink().'" class="weselny-back">← Powrót</a></p>';
echo '<h2>Rozstaw stołów</h2>';


/* SEARCH */

if($search_enabled){
echo '
<div class="stoly-search">
<input type="text" id="weselny-search" placeholder="🔍 Szukaj gościa...">
</div>
';
}


/* TABLES */

foreach($data as $table){

echo '<div class="stoly-card">';

echo '<div class="stoly-header">'.esc_html($table['title']).'</div>';

$hasSeat = false;
foreach($table['guests'] as $g){
if(!empty($g['seat'])) $hasSeat = true;
}

foreach($table['guests'] as $g){

echo '<div class="guest-item">';
echo '<span class="guest-name">'.esc_html($g['name']).'</span>';

if($hasSeat){
echo '<span class="guest-seat">'.esc_html($g['seat']).'</span>';
}

echo '</div>';

}

echo '</div>';

}

echo '</div>';
?>

<?php if($search_enabled): ?>

<script>

document.addEventListener("DOMContentLoaded", function(){

const input = document.getElementById("weselny-search");

if(!input) return;

input.addEventListener("input", function(){

let value = input.value.toLowerCase();

document.querySelectorAll(".stoly-card").forEach(card => {

let match = false;

card.querySelectorAll(".guest-item").forEach(row => {

let nameEl = row.querySelector(".guest-name");
let original = nameEl.innerText;
let text = original.toLowerCase();

if(text.includes(value)){

row.style.display = "";
match = true;

if(value){
let regex = new RegExp(`(${value})`, "gi");
nameEl.innerHTML = original.replace(regex, '<span class="highlight">$1</span>');
}else{
nameEl.innerText = original;
}

}else{

row.style.display = value ? "none" : "";
nameEl.innerText = original;

}

});

card.style.display = match || value === "" ? "" : "none";

});

});

});

</script>

<?php endif; ?>

<?php

return;
}


/* =========================
   BLOKADA
========================= */

$active = weselny_get_active_module();

if($active && $active !== 'stoly'){
return;
}


/* =========================
   KAFEL
========================= */

$url = add_query_arg('stoly','1',get_permalink($post_id));

echo '<div class="weselny-tile">';
echo '<a href="'.esc_url($url).'">Rozstaw stołów</a>';
echo '</div>';

}

add_action('weselny_render_module_stoly','weselny_stoly_render');