<?php

$setPagesBool = isset($jr_slider_pages) ? $jr_slider_pages : true;

function jr_slider_setup() {
  global $setPagesBool;

  if ($setPagesBool) {
    wp_enqueue_script("jr_slider_script", get_stylesheet_directory_uri().'/jr-slider-lite/inc/jr-slider2.js', false, false, true );
    wp_enqueue_style("jr_slider_style", get_stylesheet_directory_uri().'/jr-slider-lite/inc/jr-slider.min.css' );

    //adding localisation for ajax queries
    wp_localize_script( 'jr_slider_script', 'fileSrc', [
      'admin' => admin_url( 'admin-ajax.php' )
    ]);

  }
}
add_action( 'wp_enqueue_scripts', 'jr_slider_setup' , 9);

//get the slider
function jr_slider($args) {

  $defaults = [
    'gallery' => [],
    'has_dots' => true,
    'has_thumbs' => true,
    'has_arrow' => true,
    'max-height' => 'none',
    'autoplay' => 7000,
    'animation-type' => 'fade', //'fade' or 'slide'
    'animation-duration' => '300' //ms
  ];

  $opts = wp_parse_args($args, $defaults);
  $n = count($opts['gallery']);

  //var_dump($opts['gallery']);

  include(locate_template('jr-slider-lite/inc/jr-slider2.php'));
}

// ajax image getter. returns the rwd image string
function jr_imageGet() {

  $out['err'] = '';

  if ($_SERVER["REQUEST_METHOD"] == "POST") {


    $out = wp_get_attachment_image( $_POST['id'], 'full');

  } else {
    // Not a POST request, set a 403 (forbidden) response code.
    http_response_code(403);
    $out['err'] = 'response_forbidden';
  }

  echo json_encode($out);
  wp_die();
}

add_action('wp_ajax_gallery_img_get', 'jr_imageGet');
add_action('wp_ajax_nopriv_gallery_img_get', 'jr_imageGet');

function jr_image_placehold($imgArr) {
  $img = wp_get_attachment_image( $imgArr['id'], 'full' );
  $desc = !empty($imgArr['description']) ? '' : 'data-desc="'.$imgArr['description'].'"';
  $title = !empty($imgArr['title']) ? '' : 'data-title="'.$imgArr['title'].'"';
  $caption = !empty($imgArr['caption']) ? '' : 'data-caption="'.$imgArr['caption'].'"';

  return sprintf('<div data-img="%s" %s %s %s ></div>', $img, $desc, $title, $caption);
}


//takes the source of the images (wp gallery, acf gallery or just array of numbers), and creates assoc array to match ACF gallery
function jr_get_image_ids($imgInput) {

  if (is_string($imgInput) && has_shortcode( $imgInput, 'gallery' )) {
    // wp gallery shortcode

    preg_match('/\[gallery.*ids=.(.*?).\]/', $imgInput, $ids);
    $out = explode(",", $ids[1]);

  } elseif ( is_array($imgInput) && is_array($imgInput[0]) && array_key_exists( 'id' , $imgInput[0] ) ) {
    //acf array, by testing for the first item in the array's ID

    //$out = array_column($imgInput, 'id');
    $out = array_map(function($el) { return $el['id']; }, $imgInput);

  } elseif ( is_array($imgInput) && is_int($imgInput[0]) )  {
    //array of ids, by testing for the first item in the array is an integer

    $out = $imgInput;

  } else {
    //if fails to pass the above, return an empty array
    $out = [];
  }

  return $out;
}

// setup the post id data to match ACF, where needed
function wp_get_attachment( $attachment_id ) {

  $attachment = get_post( $attachment_id );
  return array(
    'id' => $attachment_id,
    'title' => $attachment->post_title,
    'caption' => $attachment->post_excerpt,
    'description' => $attachment->post_content
  );
}
