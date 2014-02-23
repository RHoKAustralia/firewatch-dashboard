<?php
/**
 * @package FireWatch Dashboard 
 * @version 0.2
 */
/*
  Plugin Name: FireWatch Dashboard
  Plugin URI:
  Description: Simple Plugin for FireWatch feeds (Fire Danger Rating)
  Author: RHoK and Warrandyte Community
  Version: 0.2
*/

error_reporting(1);

include('functions.php');
include('settings.php');

function fdr_wrapper( $atts, $content = null ) {
  return '<div class="fw-wrapper">';
}
add_shortcode('fdr_wrapper', 'fdr_wrapper');

function fdr_wrapper_end( $atts, $content = null ) {
  return '</div>';
}
add_shortcode('fdr_wrapper', 'fdr_wrapper');
add_shortcode('fdr_wrapper_end', 'fdr_wrapper_end');

include_once('widgets/fire_danger_rating.php');
include_once('widgets/weather_forecast.php');
include_once('widgets/fire_danger_rating_forecast.php');
include_once('widgets/twitter_feed.php');


function fire_watch_content( $atts, $content = null ) {
  $options = get_option('fire_watch_options');
  $district = $options['cfa_district'];
  $woeid = $options['woeid'];
  $bom_area = $options['bom_area'];
  $twitter_timeline = $options['twitter_timeline'];
  $content = '
<div class="fw-wrapper">
  <div class="row">
    <div class="col six">
      <div class="widget-box">
        <h2>Current Fire Danger Rating</h2>
        '.do_shortcode('[fire_danger_rating district="'.$district.'"]').'
      </div>
      <div class="widget-box">
        <h2>Current Weather Conditions</h2>
        '.do_shortcode('[weather_forecast temperature_unit="c" woeid="'.$woeid.'"]').'
      </div>
      <div class="widget-box">
        <h2>Fire Danger Rating Forecast</h2>
        '.do_shortcode('[day_forecast district="'.$district.'" bom_area="'.$bom_area.'"]').'
      </div>

      <div class="widget-box chart-widget" style="background: none repeat scroll 0 0 #fff">
        <a href="http://www.cfa.vic.gov.au/warnings-restrictions/about-fire-danger-ratings/" target="_blank"><br />
          <img alt="" src="http://www.cfa.vic.gov.au/fm_files/img/warnings-restrictions/fdr-chart.gif" width="98%" /><br />
          <span>CFA Website – Info About Fire Danger Ratings</span><br>
        </a>
      </div>
    </div>
    <div class="col six">
      <div class="widget-box twitter">
        '.$twitter_timeline.'
        <div class="below-twitter">The information in the Twitter feed above is sourced from members of the community. Use it only as one of many sources of information to assist your decision making.<br>
           <a class="yellow-button" href="http://www.aurora.asn.au/fire-watch/information-on-twitter/">Information &amp; Instructions</a>
        </div>
      </div>
    </div>
  </div>

  <div class="row cfa-buttons">
    <div class="col six">
      <div>
        <a class="widget-box button-style vic-roads" target="_blank" href="http://alerts.vicroads.vic.gov.au/melbourne-metropolitan/nillumbik">
          <div class="col six button-bg">&nbsp;</div>
          <div class="col six button-content">Closures &amp; <br>Traffic Alerts</div>
          <div class="clear"></div>
        </a>
      </div>
    </div>  
    <div class="col six">
      <div>
        <a class="widget-box button-style cfa" target="_blank" href="http://www.cfa.vic.gov.au/warnings-restrictions/warnings-and-incidents/">
          <div class="col four button-bg">&nbsp;</div>
          <div class="col eight button-content">Warnings &amp; <br>Incident Updates</div>
          <div class="clear"></div>
        </a>
      </div>
    </div>
  </div>
</div>
  ';
  return $content;
}
add_shortcode('fire_watch_content', 'fire_watch_content');

// ----------------------------------

add_filter('the_posts', 'fw_scripts_and_styles');
function fw_scripts_and_styles($posts) {
  if (empty($posts)) return $posts;

  $shortcode_found = false; // use this flag to see if styles and scripts need to be enqueued
  foreach ($posts as $post) {
    if (stripos($post->post_content, '[fire_watch_content') !== false) {
      $shortcode_found = true; // bingo!
      break;
    }
  }

  if ($shortcode_found) {
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-timeago', plugins_url('js/jquery.timeago.js', __FILE__));
    wp_enqueue_script('jquery-simpleweather', plugins_url('js/jquery.simpleWeather.js', __FILE__));
    wp_enqueue_script('fire-watch-js', plugins_url('js/plugin.js', __FILE__));

    wp_enqueue_style('fire-watch-style', plugins_url('style.css', __FILE__));
  }

  return $posts;
}

?>