<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* opcja w funkcjach panelu */

function weselny_stoly_option(){

$enabled = get_user_meta(get_current_user_id(),'weselny_modul_stoly',true);

?>

<p><strong>Lista stołów</strong></p>

<label>
<input type="checkbox" name="stoly_enabled" <?php checked($enabled,1); ?>>

</label>

<hr>

<?php

}

add_action('weselny_panel_features','weselny_stoly_option');


/* zapis ustawienia */

function weselny_stoly_save(){

if(isset($_POST['weselny_features_save'])){

$enabled = isset($_POST['stoly_enabled']) ? 1 : 0;

update_user_meta(get_current_user_id(),'weselny_modul_stoly',$enabled);

add_action('wp_footer',function(){

});

}

}

add_action('init','weselny_stoly_save');


/* kafelek */

function weselny_stoly_tile(){

$enabled = get_user_meta(get_current_user_id(),'weselny_modul_stoly',true);

if($enabled){

echo '<div class="weselny-tile" >';
echo '<a href="'.wc_get_account_endpoint_url('panel-wesela').'?stoly=1">Lista stołów</a>';
echo '</div>';

}

}

add_action('weselny_panel_tiles','weselny_stoly_tile');


/* panel stołów */

function weselny_panel_stoly(){

if(!isset($_GET['stoly'])) return;

$user_id = get_current_user_id();

$wedding = get_posts(array(
'post_type'=>'wesela',
'meta_key'=>'user_id',
'meta_value'=>$user_id,
'posts_per_page'=>1
));

if(!$wedding) return;

$post_id = $wedding[0]->ID;

$data = get_post_meta($post_id,'weselny_stoly',true);

if(!$data) $data = array();


/* NOWY STÓŁ */

if(isset($_POST['nowy_stol']) && !empty($_POST['title'])){

$data[] = array(
'title'=>sanitize_text_field($_POST['title']),
'guests'=>array()
);

update_post_meta($post_id,'weselny_stoly',$data);

}


/* USUŃ STÓŁ */

if(isset($_POST['usun_stol'])){

$index = intval($_POST['stol_index']);

unset($data[$index]);

$data = array_values($data);

update_post_meta($post_id,'weselny_stoly',$data);

}


/* ZAPIS GOŚCI */

if(isset($_POST['zapisz_gosci'])){

$index = intval($_POST['stol_index']);

$names = isset($_POST['guest_name']) ? $_POST['guest_name'] : array();
$seats = isset($_POST['seat']) ? $_POST['seat'] : array();

$guests = array();

foreach($names as $k=>$name){

if(trim($name)!=''){

$guests[] = array(
'name'=>sanitize_text_field($name),
'seat'=>sanitize_text_field($seats[$k])
);

}

}

$data[$index]['guests'] = $guests;

update_post_meta($post_id,'weselny_stoly',$data);

add_action('wp_footer',function(){
echo "<script>alert('Stoły zostały zapisane');</script>";
});

}


/* WYŚWIETLANIE */

echo '<h2>Lista stołów</h2>';

echo '<form method="post">';
echo '<input type="text" name="title" placeholder="Nagłówek stołu">';
echo '<button name="nowy_stol">Nowy stół</button>';
echo '</form>';

echo '<hr>';

foreach($data as $i=>$table){

echo '<h3>'.$table['title'].'</h3>';

echo '<form method="post">';

echo '<input type="hidden" name="stol_index" value="'.$i.'">';

echo '<div class="guest-wrapper">';

if(!empty($table['guests'])){

foreach($table['guests'] as $g){

echo '<div class="guest-row">';

echo '<input type="text" name="guest_name[]" value="'.$g['name'].'" placeholder="Nazwa gościa">';

echo '<input type="text" name="seat[]" value="'.$g['seat'].'" placeholder="Numer miejsca (opcjonalne)">';

echo '<button type="button" class="remove-guest">Usuń</button>';

echo '</div>';

}

}

echo '</div>';

echo '<br>';

echo '<button type="button" class="add-guest">Dodaj gościa</button>';

echo '<br><br>';

echo '<button name="zapisz_gosci">Zapisz</button> ';
echo '<button name="usun_stol">Usuń stół</button>';

echo '</form>';

echo '<hr>';

}

?>

<script>

document.addEventListener("click",function(e){

if(e.target.classList.contains("add-guest")){

let wrapper=e.target.closest("form").querySelector(".guest-wrapper")

let row=document.createElement("div")

row.className="guest-row"

row.innerHTML=`<input type="text" name="guest_name[]" placeholder="Nazwa gościa">
<input type="text" name="seat[]" placeholder="Numer miejsca (opcjonalne)">
<button type="button" class="remove-guest">Usuń</button>`

wrapper.appendChild(row)

}

if(e.target.classList.contains("remove-guest")){

e.target.parentNode.remove()

}

})

</script>

<?php

}

add_action('woocommerce_account_panel-wesela_endpoint','weselny_panel_stoly');