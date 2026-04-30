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

        /* 🔥 PARTICLES FLAG */
        $particles_enabled = $data['particles_enabled'] ?? 1;

        /* =========================
           STYLE
        ========================= */

        $style = '
        <style>

        .weselny-banner{
            position:relative;
            height:300px;
            overflow:hidden;
            margin-bottom:20px;
        }

        .weselny-banner img{
            width:100%;
            height:100%;
            object-fit:cover;
        }

        /* 🔥 GRADIENT OVERLAY */
        .weselny-banner::after{
            content:"";
            position:absolute;
            left:0;
            right:0;
            bottom:0;
            height:50%;
            background:linear-gradient(to top, rgba(255,255,255,1), rgba(255,255,255,0));
        }

        /* CONTENT */
        .weselny-banner-content{
            position:absolute;
            bottom:15px;
            left:15px;
            right:15px;
            z-index:2;
        }

        .weselny-banner-content h2{
            margin:0 0 5px;
            font-size:20px;
            font-weight:700;
        }

        .weselny-banner-content p{
            margin:2px 0;
            font-size:13px;
            color:#444;
        }

        /* PARTICLES */
        #weselny-particles{
            position:absolute;
            inset:0;
            z-index:1;
            pointer-events:none;
        }

        @media(max-width:480px){

            .weselny-banner{
                height:220px;
            }

            .weselny-banner-content h2{
                font-size:18px;
            }

        }

        </style>
        ';

        /* =========================
           BANNER
        ========================= */

        $banner_html = '
        '.$style.'
        <div class="weselny-banner">
            <img src="'.esc_url($banner).'">

            '.($particles_enabled ? '<div id="weselny-particles"></div>' : '').'

            <div class="weselny-banner-content">
                <h2 class="main-h2">'.esc_html($naglowek).'</h2>
                '.($podpis ? '<p>'.esc_html($podpis).'</p>' : '').'
                '.($kosciol ? '<p>'.esc_html($kosciol).'</p>' : '').'
                '.($miejsce ? '<p>'.esc_html($miejsce).'</p>' : '').'
            </div>
        </div>
        ';

        /* =========================
           RESZTA (bez zmian)
        ========================= */

        $labels = get_post_meta($post_id,'weselny_module_labels',true);
        if(!is_array($labels)) $labels = [];

        $active = weselny_get_active_module();

        if(isset($_GET['custom'])){
            $active = 'custom';
        }

        /* CUSTOM VIEW */

        if($active === 'custom' && isset($_GET['custom_id'])){

            $custom = get_post_meta($post_id,'weselny_custom_modules',true);
            $i = intval($_GET['custom_id']);

            if(!empty($custom[$i])){

                $mod = $custom[$i];
                $html = '<p><a href="'.get_permalink().'">← Powrót</a></p>';

                if(!empty($mod['blocks'])){
                    foreach($mod['blocks'] as $b){

                        $type = $b['type'] ?? '';
                        $value = $b['value'] ?? '';

                        if($type=='h1'){
                            $html .= '<h1>'.esc_html($value).'</h1>';
                        }
                        elseif($type=='h2'){
                            $html .= '<h3>'.esc_html($value).'</h3>';
                        }
                        elseif($type=='text'){
                            $html .= '<div>'.$value.'</div>';
                        }
                        elseif($type=='img' && !empty($value)){
                            $html .= '<img src="'.esc_url($value).'" style="width:100%;max-width:400px;">';
                        }
                    }
                }

                return '
                <div class="weselny-main-content">
                    '.$banner_html.'
                    <div class="weselny-content-inner">
                        '.$html.'
                    </div>
                </div>
                ';
            }
        }

        /* KOLEJNOŚĆ */

        $order = get_post_meta($post_id,'weselny_module_order',true);
        $custom_modules = get_post_meta($post_id,'weselny_custom_modules',true);

        if(!is_array($order)) $order = [];
        if(!is_array($custom_modules)) $custom_modules = [];

        $output = '';

        foreach($order as $module){

            if($active){

                if($active === 'custom' && strpos($module,'custom_') === 0){
                }
                elseif($active !== $module){
                    continue;
                }
            }

            if(strpos($module,'custom_') === 0){

                $index = intval(str_replace('custom_','',$module));

                if(isset($custom_modules[$index])){

                    $default = $custom_modules[$index]['title'] ?? 'Sekcja';
                    $key = 'custom_'.$index;

                    $title = !empty($labels[$key]) ? $labels[$key] : $default;

                    $url = add_query_arg([
                        'custom' => 1,
                        'custom_id' => $index
                    ], get_permalink());

                    $output .= '<div class="weselny-tile">';
                    $output .= '<a href="'.esc_url($url).'">';
                    $output .= esc_html($title);
                    $output .= '</a>';
                    $output .= '</div>';
                }

            } else {

                ob_start();
                do_action('weselny_render_module_'.$module, $post_id);
                $module_html = ob_get_clean();

                if(!empty(trim($module_html))){

                    if(!empty($labels[$module]) && strpos($module_html,'weselny-tile') !== false){

                        $new_label = esc_html($labels[$module]);

                        $module_html = preg_replace(
                            '/(<a[^>]*>)(.*?)(<\/a>)/',
                            '$1'.$new_label.'$3',
                            $module_html,
                            1
                        );
                    }

                    $output .= $module_html;
                }
            }
        }

        return '
        <div class="weselny-main-content">
            '.$banner_html.'
            <div class="weselny-content-inner">
                <div class="weselny-slide">
                    '.$output.'
                </div>
            </div>
        </div>
        ';
    }

    return $content;
}

add_filter( 'the_content', 'wp_weselny_panel_gosci_content' );