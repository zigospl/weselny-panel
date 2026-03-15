<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Pole w Funkcjach panelu
 */

function weselny_stoly_option() {

    $enabled = get_user_meta(get_current_user_id(),'weselny_modul_stoly',true);

    ?>

    <p><strong>Lista stołów</strong></p>

    <label>
        <input type="checkbox" name="stoly_enabled" <?php checked($enabled,1); ?>>
        Włącz
    </label>

    <br><br>

    <button type="submit" name="stoly_save">Zapisz</button>

    <hr>

    <?php

}

add_action('weselny_panel_features','weselny_stoly_option');


/**
 * zapis
 */

function weselny_stoly_save(){

    if(isset($_POST['stoly_save'])){

        $enabled = isset($_POST['stoly_enabled']) ? 1 : 0;

        update_user_meta(get_current_user_id(),'weselny_modul_stoly',$enabled);

    }

}

add_action('init','weselny_stoly_save');


/**
 * kafelek w panelu
 */

function weselny_stoly_tile(){

$enabled = get_user_meta(get_current_user_id(),'weselny_modul_stoly',true);

if($enabled){

echo '<div style="border:1px solid #ccc;padding:20px;display:inline-block;margin:10px;">Lista stołów</div>';

}

}

add_action('weselny_panel_tiles','weselny_stoly_tile');