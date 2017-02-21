<?php

$setPagesBool = isset($jr_slider_pages) ? $jr_slider_pages : true;

function jr_slider_setup() {
  global $setPagesBool;

  if ($setPagesBool) {
    wp_enqueue_script("jr_slider_script", get_stylesheet_directory_uri().'/jr-slider-lite/inc/jr-slider2.min.js', false, false, true );
    wp_enqueue_style("jr_slider_style", get_stylesheet_directory_uri().'/jr-slider-lite/inc/jr-slider.min.css' );
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
