<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* =========================
   HELPER
========================= */

function weselny_collect_custom($post){

    $result = [];

    if(empty($post['modules'])) return $result;

    foreach($post['modules'] as $mod){

        $title = sanitize_text_field($mod['title'] ?? '');
        $blocks = [];

        if(!empty($mod['blocks'])){
            foreach($mod['blocks'] as $b){

                $type = sanitize_text_field($b['type'] ?? '');

                if($type === 'text'){
                    $value = wp_kses_post($b['value'] ?? '');
                } elseif($type === 'img'){
                    $value = esc_url_raw($b['value'] ?? '');
                } else {
                    $value = sanitize_text_field($b['value'] ?? '');
                }

                $blocks[] = [
                    'type'=>$type,
                    'value'=>$value
                ];
            }
        }

        $result[] = [
            'title'=>$title,
            'blocks'=>$blocks
        ];
    }

    return $result;
}

/* =========================
   AJAX UPLOAD
========================= */

add_action('wp_ajax_weselny_upload_image', 'weselny_upload_image');
function weselny_upload_image(){

    if(empty($_FILES['file'])){
        wp_send_json_error();
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $attachment_id = media_handle_upload('file', 0);

    if(is_wp_error($attachment_id)){
        wp_send_json_error();
    }

    $url = wp_get_attachment_url($attachment_id);

    wp_send_json_success(['url'=>$url]);
}

/* =========================
   TILE
========================= */

function weselny_custom_tile(){
    echo '<div class="weselny-tile">';
    echo '<a href="'.wc_get_account_endpoint_url('panel-wesela').'?custom=1">Własne sekcje</a>';
    echo '</div>';
}
add_action('weselny_panel_tiles','weselny_custom_tile');

/* =========================
   PANEL
========================= */

function weselny_custom_panel(){

if(!isset($_GET['custom'])) return;

$user_id = get_current_user_id();

$wedding = get_posts([
    'post_type'=>'wesela',
    'meta_key'=>'user_id',
    'meta_value'=>$user_id,
    'posts_per_page'=>1
]);

if(!$wedding) return;

$post_id = $wedding[0]->ID;

$data = get_post_meta($post_id,'weselny_custom_modules',true);
if(!is_array($data)) $data = [];

if(isset($_POST['save_all'])){
    $data = weselny_collect_custom($_POST);
    update_post_meta($post_id,'weselny_custom_modules',$data);
}

/* =========================
   UI
========================= */

echo '<style>
#weselny-custom{max-width:900px;margin:0 auto;font-family:Arial;}
.module-box{background:#fff;border-radius:12px;padding:20px;margin-bottom:20px;box-shadow:0 4px 12px rgba(0,0,0,0.08);}
.block-box{background:#fafafa;border-radius:10px;padding:15px;margin-bottom:15px;border:1px solid #eee;}
.add-btn{background:#1a3d1a;color:#fff;border:none;padding:8px 12px;border-radius:6px;cursor:pointer;margin:5px 5px 0 0;}
.remove-btn{background:#c0392b;color:#fff;border:none;padding:6px 10px;border-radius:6px;cursor:pointer;float:right;}
.preview-img{max-width:120px;margin-top:10px;border-radius:8px;}
.ql-editor{min-height:120px;}
.heading-input{font-size:20px;font-weight:600;padding:12px;border:2px solid #ddd;border-radius:8px;}
.heading-input.h2{font-size:16px;}
.label{font-size:12px;color:#666;margin-bottom:5px;display:block;}
</style>';

echo '<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">';
echo '<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>';

echo '<div id="weselny-custom">';
echo '<h2>Własne sekcje</h2>';

echo '<form method="post" id="custom-form">';
echo '<div id="modules-wrapper">';

/* EXISTING */

foreach($data as $i=>$mod){

echo '<div class="module-box">';
echo '<button type="button" class="remove-btn remove-module">Usuń</button>';
echo '<input type="text" class="module-title" name="modules['.$i.'][title]" value="'.esc_attr($mod['title']).'" placeholder="Tytuł sekcji"><br><br>';

echo '<div class="blocks-wrapper">';

foreach($mod['blocks'] ?? [] as $k=>$b){

$type = $b['type'];
$value = $b['value'];

echo '<div class="block-box">';
echo '<input type="hidden" class="block-type" name="modules['.$i.'][blocks]['.$k.'][type]" value="'.$type.'">';
echo '<button type="button" class="remove-btn remove-block">X</button>';

if($type==='img'){
echo '<input type="file" class="img-upload">';
echo '<input type="hidden" class="img-value" name="modules['.$i.'][blocks]['.$k.'][value]" value="'.esc_url($value).'">';
echo '<img src="'.esc_url($value).'" class="preview-img">';
}
elseif($type==='text'){
echo '<div class="quill-editor">'.$value.'</div>';
echo '<input type="hidden" class="text-value" name="modules['.$i.'][blocks]['.$k.'][value]" value="'.esc_attr($value).'">';
}
elseif($type==='h1'){
echo '<span class="label">H1</span>';
echo '<input type="text" class="heading-input" name="modules['.$i.'][blocks]['.$k.'][value]" value="'.esc_attr($value).'">';
}
elseif($type==='h2'){
echo '<span class="label">H2</span>';
echo '<input type="text" class="heading-input h2" name="modules['.$i.'][blocks]['.$k.'][value]" value="'.esc_attr($value).'">';
}

echo '</div>';
}

echo '</div>';

echo '
<button type="button" class="add-btn add-img">+ Obrazek</button>
<button type="button" class="add-btn add-h1">+ H1</button>
<button type="button" class="add-btn add-h2">+ H2</button>
<button type="button" class="add-btn add-text">+ Tekst</button>
';

echo '</div>';
}

echo '</div>';

echo '<button type="button" id="add-module" class="add-btn">+ Dodaj sekcję</button><br><br>';
echo '<button name="save_all" class="add-btn">Zapisz</button>';

echo '</form>';
echo '</div>';

/* =========================
   JS
========================= */

echo '<script>

function initQuill(container){
container.querySelectorAll(".quill-editor").forEach(el=>{
if(el.classList.contains("init")) return;

let hidden = el.nextElementSibling;
let q = new Quill(el,{theme:"snow"});

q.root.innerHTML = hidden.value;

q.on("text-change",()=>{
hidden.value = q.root.innerHTML;
});

el.classList.add("init");
});
}

/* ADD MODULE */
document.getElementById("add-module").addEventListener("click", function(){

let wrapper = document.getElementById("modules-wrapper");

let div = document.createElement("div");
div.className="module-box";

div.innerHTML = `
<button type="button" class="remove-btn remove-module">Usuń</button>
<input type="text" class="module-title" placeholder="Tytuł sekcji"><br><br>
<div class="blocks-wrapper"></div>

<button type="button" class="add-btn add-img">+ Obrazek</button>
<button type="button" class="add-btn add-h1">+ H1</button>
<button type="button" class="add-btn add-h2">+ H2</button>
<button type="button" class="add-btn add-text">+ Tekst</button>
`;

wrapper.appendChild(div);
});

/* CLICK */
document.addEventListener("click",function(e){

if(e.target.classList.contains("remove-module")){
e.target.closest(".module-box").remove();
}

if(e.target.classList.contains("remove-block")){
e.target.closest(".block-box").remove();
}

if(e.target.classList.contains("add-img")) addBlock(e,"img");
if(e.target.classList.contains("add-h1")) addBlock(e,"h1");
if(e.target.classList.contains("add-h2")) addBlock(e,"h2");
if(e.target.classList.contains("add-text")) addBlock(e,"text");

});

function addBlock(e,type){

let wrapper = e.target.closest(".module-box").querySelector(".blocks-wrapper");

let div = document.createElement("div");
div.className="block-box";

let html = `<input type="hidden" class="block-type" value="${type}">
<button type="button" class="remove-btn remove-block">X</button>`;

if(type==="img"){
html += `<input type="file" class="img-upload">
<input type="hidden" class="img-value">
<img class="preview-img" style="display:none;">`;
}
else if(type==="text"){
html += `<div class="quill-editor"></div>
<input type="hidden" class="text-value">`;
}
else if(type==="h1"){
html += `<span class="label">H1</span><input type="text" class="heading-input">`;
}
else if(type==="h2"){
html += `<span class="label">H2</span><input type="text" class="heading-input h2">`;
}

div.innerHTML = html;
wrapper.appendChild(div);

initQuill(div);
}

/* UPLOAD */
document.addEventListener("change", function(e){

if(e.target.classList.contains("img-upload")){

let file = e.target.files[0];
let formData = new FormData();

formData.append("action","weselny_upload_image");
formData.append("file",file);

fetch("'.admin_url('admin-ajax.php').'",{
method:"POST",
body:formData
})
.then(r=>r.json())
.then(res=>{
if(res.success){

let box = e.target.closest(".block-box");
box.querySelector(".img-value").value = res.data.url;

let img = box.querySelector(".preview-img");
img.src = res.data.url;
img.style.display="block";

}
});
}
});

/* REINDEX */
document.getElementById("custom-form").addEventListener("submit",function(){

document.querySelectorAll(".module-box").forEach((module,mIndex)=>{

let title = module.querySelector(".module-title");
if(title) title.name = `modules[${mIndex}][title]`;

module.querySelectorAll(".block-box").forEach((block,bIndex)=>{

let type = block.querySelector(".block-type");
if(type) type.name = `modules[${mIndex}][blocks][${bIndex}][type]`;

let val;

if(block.querySelector(".img-value")){
val = block.querySelector(".img-value");
}
else if(block.querySelector(".text-value")){
val = block.querySelector(".text-value");
}
else{
val = block.querySelector("input[type=text]");
}

if(val){
val.name = `modules[${mIndex}][blocks][${bIndex}][value]`;
}

});

});

});

document.addEventListener("DOMContentLoaded",()=>initQuill(document));

</script>';

}

add_action('woocommerce_account_panel-wesela_endpoint','weselny_custom_panel');