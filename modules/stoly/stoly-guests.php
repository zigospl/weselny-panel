<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function weselny_stoly_guest_module($content){

if(get_post_type()==='wesela' && is_singular('wesela')){

$content .= '<p>Moduł stołów w przygotowaniu</p>';

}

return $content;

}

add_filter('the_content','weselny_stoly_guest_module');