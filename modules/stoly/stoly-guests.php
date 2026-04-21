<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function weselny_stoly_render($post_id){

/* właściciel */
$user_id = get_post_meta($post_id,'user_id',true);
if(!$user_id) return;

/* czy włączony */
$enabled = get_user_meta($user_id,'weselny_modul_stoly',true);
if(!$enabled) return;

/* dane */
$data = get_post_meta($post_id,'weselny_stoly',true);
if(!$data) return;

/* 🔥 ustawienie wyszukiwarki */
$search_enabled = get_post_meta($post_id,'weselny_stoly_search',true);


/* =========================
   WIDOK STOŁÓW
========================= */

if(isset($_GET['stoly'])){

echo '<p><a href="'.get_permalink().'" class="weselny-back">← Powrót do panelu</a></p>';
echo '<h2>Rozstaw stołów</h2>';


/* 🔥 WYSZUKIWARKA */
if($search_enabled){
echo '
<input 
type="text" 
id="weselny-search" 
placeholder="🔍 Wpisz imię lub nazwisko..." 
style="width:100%;padding:10px;margin-bottom:20px;border:1px solid #ccc;border-radius:6px;"
>
';
}


foreach($data as $table){

echo '<div class="weselny-table">';

echo '<h3>'.esc_html($table['title']).'</h3>';

$hasSeat = false;

foreach($table['guests'] as $g){
if(!empty($g['seat'])) $hasSeat = true;
}

echo '<table border="1" cellpadding="6">';

foreach($table['guests'] as $g){

echo '<tr class="guest-row">';
echo '<td class="guest-name">'.esc_html($g['name']).'</td>';

if($hasSeat){
echo '<td>'.esc_html($g['seat']).'</td>';
}

echo '</tr>';

}

echo '</table><br>';

echo '</div>';
}

?>

<?php if($search_enabled): ?>

<script>

document.addEventListener("DOMContentLoaded", function(){

const input = document.getElementById("weselny-search");

if(!input) return;

input.addEventListener("input", function(){

let value = input.value.toLowerCase();

document.querySelectorAll(".weselny-table").forEach(table => {

let matchFound = false;

table.querySelectorAll(".guest-row").forEach(row => {

let nameEl = row.querySelector(".guest-name");
let text = nameEl.innerText.toLowerCase();

if(text.includes(value)){

row.style.display = "";

matchFound = true;

/* 🔥 highlight */
let original = nameEl.innerText;
let regex = new RegExp(`(${value})`, "gi");
nameEl.innerHTML = original.replace(regex, '<span style="background:yellow;">$1</span>');

} else {

row.style.display = "";
nameEl.innerHTML = nameEl.innerText;

if(value !== ""){
row.style.display = "none";
}

}

});

/* ukryj cały stół jeśli brak wyników */
table.style.display = matchFound ? "" : "none";

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