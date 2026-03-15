<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wp_weselny_panel_gosci_content( $content ) {

    if ( get_post_type() === 'wesela' && is_singular('wesela') ) {

        return '<h2>Witaj w panelu gościa</h2>';

    }

    return $content;

}

add_filter( 'the_content', 'wp_weselny_panel_gosci_content' );