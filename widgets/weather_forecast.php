<?php
define( 'FIREWATCH_ROOT_DIR', dirname(__FILE__) );
include_once(FIREWATCH_ROOT_DIR.'/../functions.php');

if(isset($_GET['independent'])) {
  $temperature_unit=$_GET['temperature_unit'];
  echo get_weather_forecast($temperature_unit);
} else {
  function weather_forecast($atts) {
    include_once('/../../../../wp-load.php');
    $bom_website="http://www.bom.gov.au/products/IDV60901/IDV60901.95874.shtml";
    extract(shortcode_atts(array(
      'district' => '',
      'temperature_unit' => ''
    ), $atts));
    $content = '<div class="widget-data">'.get_weather_forecast($temperature_unit).'</div>';
    $content .= '<div class="widget-details">';
    $content .= '<a target="_blank" onclick="javascript:_gaq.push([\'_trackEvent\',\'outbound-article\',\'http://www.bom.gov.au\']);" href="'. $bom_website .'">BOM Website</a>';
    $content .= '<abbr class="widget-time timeago" title="'.date('r').'">'.date().'</abbr><a class="refresh-widget" data-url="'.plugin_dir_url(__FILE__).basename(__FILE__).'?independent=1&temperature_unit='. $temperature_unit .'">Refresh</a></div>';
    return $content;
  }
  add_shortcode('weather_forecast', 'weather_forecast');
}

function get_weather_forecast($temperature_unit) {
  $forecast = '<div class="weather_forecast"></div>';
  $forecast .= "
<script>
jQuery.simpleWeather({
  zipcode: '',
  woeid: '1103816',
  location: '',
  unit: '" . $temperature_unit . "',
  success: function(weather) {
    html = '<h2>'+weather.temp+'&deg;'+weather.units.temp+'</h2>';
    html += '<ul><li>'+weather.wind.direction+' '+weather.wind.speed+'km/h</li>';
    html += '<li>'+weather.city+', '+weather.region+'</li>';
    html += '<li class=\"currently\">'+weather.currently+'</li></ul>';

    jQuery(\".weather_forecast\").html(html);
  },
  error: function(error) {
    jQuery(\".weather_forecast\").html('<p>'+error+'</p>');
  }
});
</script>
  ";
  return $forecast;
}
?>