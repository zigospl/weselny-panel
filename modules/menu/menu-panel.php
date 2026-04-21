<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =========================
   OPCJA WŁĄCZENIA
========================= */

function weselny_menu_option(){

$enabled = get_user_meta(get_current_user_id(),'weselny_modul_menu',true);
?>

<p><strong>Menu weselne</strong></p>

<label>
<input type="checkbox" name="menu_enabled" <?php checked($enabled,1); ?>>
</label>

<hr>

<?php
}
add_action('weselny_panel_features','weselny_menu_option');


/* =========================
   ZAPIS WŁĄCZENIA
========================= */

function weselny_menu_save(){

if(isset($_POST['weselny_features_save'])){

$enabled = isset($_POST['menu_enabled']) ? 1 : 0;
update_user_meta(get_current_user_id(),'weselny_modul_menu',$enabled);

}

}
add_action('init','weselny_menu_save');


/* =========================
   KAFEL
========================= */

function weselny_menu_tile(){

$enabled = get_user_meta(get_current_user_id(),'weselny_modul_menu',true);

if($enabled){
echo '<div class="weselny-tile">';
echo '<a href="'.wc_get_account_endpoint_url('panel-wesela').'?menu=1">Menu weselne</a>';
echo '</div>';
}

}
add_action('weselny_panel_tiles','weselny_menu_tile');


/* =========================
   PANEL
========================= */

function weselny_panel_menu(){

if(!isset($_GET['menu'])) return;

$user_id = get_current_user_id();

$wedding = get_posts([
'post_type'=>'wesela',
'meta_key'=>'user_id',
'meta_value'=>$user_id,
'posts_per_page'=>1
]);

if(!$wedding) return;

$post_id = $wedding[0]->ID;

$data = get_post_meta($post_id,'weselny_menu',true);
if(!$data) $data = [];


/* =========================
   ZAPIS
========================= */

$saved = false;

if(isset($_POST['save_all'])){

$new_data = [];

if(isset($_POST['sections'])){
foreach($_POST['sections'] as $section){

$new_data[] = [
'title' => sanitize_text_field($section['title'] ?? ''),
'content' => wp_kses_post($section['content'] ?? '')
];

}
}

update_post_meta($post_id,'weselny_menu',$new_data);
$data = $new_data;
$saved = true;

}


/* =========================
   UI
========================= */

echo '<h2>Menu weselne</h2>';
echo '<p><a href="'.wc_get_account_endpoint_url('panel-wesela').'">← Powrót</a></p>';

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

echo '<div id="menu-sections">';

foreach($data as $i=>$section){

echo '<div class="menu-section" style="border:1px solid #ccc;padding:15px;margin-bottom:20px;">';

echo '<h3>Sekcja '.($i+1).'</h3>';

echo '<input class="text-input-block" type="text" name="sections['.$i.'][title]" value="'.esc_attr($section['title']).'" placeholder="Nagłówek"><br><br>';

wp_editor(
$section['content'],
'content_'.$i,
[
'textarea_name'=>'sections['.$i.'][content]',
'textarea_rows'=>5
]
);

echo '<br>';

echo '<button type="button" class="remove-section">Usuń</button>';

echo '</div>';

}

echo '</div>';

echo '<button type="button" id="add-section">+ Dodaj sekcję</button><br><br>';

echo '<button name="save_all" style="font-size:16px;padding:10px 20px;">Zapisz</button>';

echo '</form>';
?>

<script>

let sectionIndex = <?php echo count($data); ?>;

document.getElementById("add-section").addEventListener("click", function(){

    let container = document.getElementById("menu-sections");

    let div = document.createElement("div");
    div.className = "menu-section";
    div.style = "border:1px solid #ccc;padding:15px;margin-bottom:20px;";

    div.innerHTML = `
        <h3>Sekcja ${sectionIndex + 1}</h3>

        <input class="text-input-block" type="text" name="sections[${sectionIndex}][title]" placeholder="Nagłówek"><br><br>

        <textarea name="sections[${sectionIndex}][content]" rows="5"></textarea><br>

        <button type="button" class="remove-section">Usuń</button>
    `;

    container.appendChild(div);

    sectionIndex++;
});


document.addEventListener("click", function(e){

    if(e.target.classList.contains("remove-section")){
        e.target.closest(".menu-section").remove();
    }

});


<?php if($saved): ?>

document.addEventListener("DOMContentLoaded", function(){
    const msg = document.getElementById("weselny-save-msg");

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

add_action('woocommerce_account_panel-wesela_endpoint','weselny_panel_menu');