<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/* =========================
   HELPER – OBSŁUGA OBRAZKA
========================= */

function weselny_get_image_src($value){

    if(empty($value)) return '';

    if(strpos($value, 'data:image') === 0){
        return $value;
    }

    if(is_numeric($value)){
        $url = wp_get_attachment_url($value);
        if($url) return $url;
    }

    if(filter_var($value, FILTER_VALIDATE_URL)){
        return $value;
    }

    return '';
}


/* =========================
   RENDER MODUŁU
========================= */

function weselny_custom_render($post_id){

$data = get_post_meta($post_id,'weselny_custom_modules',true);

if(!$data || !is_array($data)) return;


/* =========================
   🔥 DEBUG (tymczasowo możesz odkomentować)
========================= */
// echo '<pre>'; print_r($data); echo '</pre>';


/* =========================
   🔥 WIDOK SEKCJI
========================= */

if(isset($_GET['custom']) && isset($_GET['custom_id'])){

    $i = intval($_GET['custom_id']);

    if(!isset($data[$i])) return;

    $mod = $data[$i];

    echo '<p><a href="'.get_permalink($post_id).'" class="weselny-back">← Powrót</a></p>';

    if(!empty($mod['blocks'])){

        foreach($mod['blocks'] as $b){

            $type = $b['type'] ?? '';
            $value = $b['value'] ?? '';

            /* ===== H1 ===== */
            if($type === 'h1'){
                echo '<h1>'.esc_html($value).'</h1>';
            }

            /* ===== H2 ===== */
            elseif($type === 'h2'){
                echo '<h3>'.esc_html($value).'</h3>';
            }

            /* ===== TEXT ===== */
            elseif($type === 'text'){
                echo '<div>'.$value.'</div>';
            }

            /* ===== IMG ===== */
            elseif($type === 'img'){

                $src = weselny_get_image_src($value);

                if(!empty($src)){
                    echo '<img src="'.esc_url($src).'" style="width:100%;max-width:400px;display:block;margin-bottom:15px;">';
                }

            }

            /* ===== SPACE (FINAL FIX) ===== */
            elseif($type === 'space'){

                // 🔥 zabezpieczenie na wszystko
                if(is_array($value)){
                    $height = 30;
                } else {
                    $height = intval($value);
                }

                if($height <= 0){
                    $height = 30;
                }

                echo '<div class="weselny-space" style="height:'.$height.'px;"></div>';
            }

        }

    }

    return;
}


/* =========================
   🔥 BLOKADA
========================= */

$active = weselny_get_active_module();

if($active && $active !== 'custom'){
    return;
}


/* =========================
   LISTA SEKCJI
========================= */

if(isset($_GET['custom'])){

    foreach($data as $i=>$mod){

        $title = !empty($mod['title']) ? $mod['title'] : 'Sekcja '.($i+1);

        $url = add_query_arg([
            'custom' => 1,
            'custom_id' => $i
        ], get_permalink($post_id));

        echo '<div class="weselny-tile">';
        echo '<a href="'.esc_url($url).'">'.esc_html($title).'</a>';
        echo '</div>';

    }

    return;
}


/* =========================
   GŁÓWNY KAFEL
========================= */

$url = add_query_arg('custom',1,get_permalink($post_id));

echo '<div class="weselny-tile">';
echo '<a href="'.esc_url($url).'">Własne sekcje</a>';
echo '</div>';

}

add_action('weselny_render_module_custom','weselny_custom_render');