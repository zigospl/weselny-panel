<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wp_weselny_panel_gosci_content( $content ) {

    if ( get_post_type() === 'wesela' && is_singular('wesela') ) {

        $post_id = get_the_ID();
        $user_id = get_post_meta($post_id,'user_id',true);

        $data = get_user_meta($user_id,'weselny_wyglad',true);

        $banner_id = $data['banner_id'] ?? '';
        $banner = $banner_id ? wp_get_attachment_url($banner_id) : '/wp-content/uploads/2026/03/pexels-caleboquendo-3023235.jpg';

        $naglowek = $data['naglowek'] ?? 'Witamy na naszym weselu!';
        $podpis = $data['podpis'] ?? '';
        $kosciol = $data['kosciol'] ?? '';
        $miejsce = $data['miejsce'] ?? '';

        $banner_html = '
        <div class="weselny-banner" style="position:relative;margin-bottom:20px;">

            <img src="'.esc_url($banner).'" style="width:100%;height:250px;object-fit:cover;">

            <div class="weselny-banner-content" >

                <h2>'.esc_html($naglowek).'</h2>
                <p>'.esc_html($podpis).'</p>
                <p>'.esc_html($kosciol).'</p>
                <p>'.esc_html($miejsce).'</p>

            </div>

            <div id="weselny-particles"></div>

        </div>
        ';

        // 🔥 opakowanie contentu
        $content = '<div class="weselny-content">'.$content.'</div>';

        return $banner_html . $content;
    }

    return $content;

}

add_filter( 'the_content', 'wp_weselny_panel_gosci_content' );