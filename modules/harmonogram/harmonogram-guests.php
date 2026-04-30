<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =========================
   RENDER MODUŁU
========================= */

function weselny_harmonogram_render($post_id){

$user_id = get_post_meta($post_id,'user_id',true);
if(!$user_id) return;

$enabled = get_user_meta($user_id,'weselny_modul_harmonogram',true);
if(!$enabled) return;


/* =========================
   WIDOK
========================= */

if(isset($_GET['harmonogram'])){

$data = get_post_meta($post_id,'weselny_harmonogram',true);

echo '
<style>

.harmonogram-container{
max-width:600px;
margin:0 auto;
font-family:Arial;
position:relative;
}

/* TIMELINE LINE */
.harmonogram-container::before{
content:"";
position:absolute;
left:20px;
top:0;
bottom:0;
width:2px;
background:#e5e5e5;
}

/* ITEM */
.h-item{
position:relative;
padding-left:50px;
margin-bottom:25px;
}

/* DOT */
.h-item::before{
content:"";
position:absolute;
left:12px;
top:5px;
width:16px;
height:16px;
background:#1a3d1a;
border-radius:50%;
border:3px solid #fff;
box-shadow:0 0 0 2px #1a3d1a20;
}

/* CARD */
.h-card{
background:#fff;
border-radius:12px;
padding:15px;
box-shadow:0 4px 12px rgba(0,0,0,0.08);
}

/* TIME */
.h-time{
font-size:13px;
color:#888;
margin-bottom:5px;
}

/* TITLE */
.h-title{
font-size:16px;
font-weight:600;
margin-bottom:6px;
}

/* DESC */
.h-desc{
font-size:14px;
color:#444;
line-height:1.5;
}

/* MOBILE */
@media (max-width:480px){

.harmonogram-container::before{
left:14px;
}

.h-item{
padding-left:40px;
}

.h-item::before{
left:6px;
}

}

</style>
';

echo '<div class="harmonogram-container">';

echo '<p><a href="'.get_permalink().'" class="weselny-back">← Powrót</a></p>';
echo '<h2>Harmonogram</h2>';

if(!empty($data)){

foreach($data as $row){

$time = $row['time'] ?? '';
$title = $row['title'] ?? '';
$desc = $row['description'] ?? '';

echo '
<div class="h-item">
    <div class="h-card">
        <div class="h-time">'.esc_html($time).'</div>
        <div class="h-title">'.esc_html($title).'</div>
        <div class="h-desc">'.$desc.'</div>
    </div>
</div>
';

}

}else{

echo '
<div style="
text-align:center;
padding:40px 20px;
border:2px dashed #ddd;
border-radius:12px;
color:#666;
background:#fafafa;
margin-top:20px;
">
<p style="font-size:20px;margin-bottom:10px;">🕒</p>
<p><strong>Harmonogram jeszcze nie został dodany</strong></p>
<p style="margin-top:10px;">Wkrótce pojawią się tutaj szczegóły wydarzenia</p>
</div>
';

}

echo '</div>';

return;
}


/* =========================
   BLOKADA
========================= */

$active = weselny_get_active_module();

if($active && $active !== 'harmonogram'){
return;
}


/* =========================
   KAFEL
========================= */

$url = add_query_arg('harmonogram','1',get_permalink($post_id));

echo '<div class="weselny-tile">';
echo '<a href="'.esc_url($url).'">Harmonogram</a>';
echo '</div>';

}

/* HOOK */
add_action('weselny_render_module_harmonogram','weselny_harmonogram_render');