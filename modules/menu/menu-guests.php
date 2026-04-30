<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =========================
   RENDER MODUŁU
========================= */

function weselny_menu_render($post_id){

$user_id = get_post_meta($post_id,'user_id',true);
if(!$user_id) return;

$enabled = get_user_meta($user_id,'weselny_modul_menu',true);
if(!$enabled) return;


/* =========================
   WIDOK MENU
========================= */

if(isset($_GET['menu'])){

$data = get_post_meta($post_id,'weselny_menu',true);

echo '
<style>

.menu-container{
max-width:650px;
margin:0 auto;
font-family:Arial;
}

/* CARD */
.menu-card{
background:#fff;
border-radius:14px;
padding:18px;
margin-bottom:18px;
box-shadow:0 6px 18px rgba(0,0,0,0.08);
}

/* TITLE */
.menu-title{
font-size:18px;
font-weight:600;
margin-bottom:12px;
padding-bottom:8px;
border-bottom:1px solid #eee;
}

/* CONTENT */
.menu-content{
font-size:14px;
color:#444;
line-height:1.6;
}

/* LIST STYLE */
.menu-content ul{
padding-left:18px;
margin:10px 0;
}

.menu-content li{
margin-bottom:6px;
}

/* MOBILE */
@media (max-width:480px){

.menu-card{
padding:14px;
}

.menu-title{
font-size:16px;
}

}

</style>
';

echo '<div class="menu-container">';

echo '<p><a href="'.get_permalink().'" class="weselny-back">← Powrót</a></p>';
echo '<h2>Menu weselne</h2>';

if(!empty($data)){

foreach($data as $section){

$title = $section['title'] ?? '';
$content = $section['content'] ?? '';

echo '
<div class="menu-card">
    <div class="menu-title">'.esc_html($title).'</div>
    <div class="menu-content">'.$content.'</div>
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
<p style="font-size:20px;margin-bottom:10px;">🍽️</p>
<p><strong>Menu jeszcze nie zostało dodane</strong></p>
<p style="margin-top:10px;">Wkrótce pojawią się tutaj wszystkie dania</p>
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

if($active && $active !== 'menu'){
return;
}


/* =========================
   KAFEL
========================= */

$url = add_query_arg('menu','1',get_permalink($post_id));

echo '<div class="weselny-tile">';
echo '<a href="'.esc_url($url).'">Menu</a>';
echo '</div>';

}

/* HOOK */
add_action('weselny_render_module_menu','weselny_menu_render');