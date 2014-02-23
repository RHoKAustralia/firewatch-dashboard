<?php
if(!defined('FIREWATCH_ROOT_DIR')) define( 'FIREWATCH_ROOT_DIR', dirname(__FILE__) );
include_once(FIREWATCH_ROOT_DIR.'/../functions.php');

if(isset($_GET['independent'])) {
  $district = $_GET['district'];
  $bom_area = $_GET['bom_area'];
  echo get_forecast_list($district, $bom_area);
} else {
  include_once(FIREWATCH_ROOT_DIR.'/../../../../wp-load.php');
  function day_forecast($atts) {
    extract(shortcode_atts(array(
      'district' => '',
      'bom_area' => '',
    ), $atts));
    $content = '<div class="widget-data fire-forecast">'.get_forecast_list($district, $bom_area).'</div>';
    $content .= '<div class="widget-details">';
    $content .= '<a target="_blank" onclick="javascript:_gaq.push([\'_trackEvent\',\'outbound-article\',\'http://www.cfa.vic.gov.au\']);" href="http://www.cfa.vic.gov.au/warnings-restrictions/'. $district .'-fire-district/">CFA Website</a>';
    $content .= '<br><a target="_blank" onclick="javascript:_gaq.push([\'_trackEvent\',\'outbound-article\',\'http://www.bom.gov.au\']);" href="http://www.bom.gov.au/vic/forecasts/'. strtolower($bom_area) .'.shtml">BOM Website</a>';
    $content .= '<abbr class="widget-time timeago" title="'.date('r').'">'.date().'</abbr><a class="refresh-widget" data-url="'.plugin_dir_url(__FILE__).basename(__FILE__).'?independent=1&district='. $district .'&bom_area='. $bom_area .'">Refresh</a></div>';
    return $content;
  }
  add_shortcode('day_forecast', 'day_forecast');
}

function get_forecast_list($district, $bom_area) {
  $data = get_weather_and_cfa_fdr_forecast($district, $bom_area);

  ob_start();
  echo $data;
  $list = ob_get_clean();
  return $list;
}

function get_bom_weather_forecast($bom_area) {
  $item_index = 0;
  $max_items = 3;
  $data = "";

  $xmlUrl = "ftp://ftp2.bom.gov.au/anon/gen/fwo/IDV10753.xml";

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $xmlUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $output = curl_exec($ch);
  curl_close($ch);

  $xmlObj = simplexml_load_string($output);
  $arrXml = objectsIntoArray($xmlObj);

  $index = 0;
  $temp_location = array(); 

  foreach($xmlObj->forecast->area as $area)
  {
    if($area['description'] == $bom_area) {
      foreach($area->{"forecast-period"} as $element_type) {
        $temp_location[$item_index] = "";
        $date = $element_type['start-time-local'];
        $low_temp = $element_type->element[1];
        $high_temp = $element_type->element[2];
        $day = date("D", strtotime($date));
        $temp_location[$item_index] .= "$day: ".$high_temp."&deg;C";
        $item_index += 1;
      }
      $temp_location[$item_index] .= "<hr/>";
      break;
    }
    $index += 1;
  }
  return $temp_location;
}

function get_weather_and_cfa_fdr_forecast($district, $bom_area) {

  $item_index = 0;
  $offset = 1;
  $max_items = 3;
  $data = "";

  $xmlUrl = "http://www.cfa.vic.gov.au/restrictions/".$district."-firedistrict_rss.xml"; // XML feed file/URL

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $xmlUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $output = curl_exec($ch);
  curl_close($ch);

  $xmlObj = simplexml_load_string($output);
  $arrXml = objectsIntoArray($xmlObj);

  $timezone = "Australia/Melbourne";
  date_default_timezone_set($timezone);

  $aest = strtotime($arrXml['channel']['pubDate']);
  //echo $arrXml[channel][pubDate];

  $bom_weather_forecast = get_bom_weather_forecast($bom_area);

  if (count($arrXml['channel']['item']) == 0) {
    $data = "No Ratings are available.";
  } else {
    while ($item_index < $max_items) {
      $title = $arrXml['channel']['item'][$item_index+$offset]['title'];
      $description = $arrXml['channel']['item'][$item_index+$offset]['description'];
      // Get danger level
      $ratingstr = explode("/images/fdr/$district/", $description);
      $ratingstr = explode(".gif", $ratingstr[1]);
      $ratingstr = explode("_tfb", $ratingstr[0]);
      $ratingstr = $ratingstr[0];
      switch ($ratingstr) {
        case "codered":
          $rating = "Code Red";
          break;
        case "extreme":
          $rating = "Extreme";
          break;
        case "severe":
          $rating = "Severe";
          break;
        case "veryhigh":
          $rating = "Very High";
          break;
        case "high":
          $rating = "High";
          break;
        case "lowtomoderate":
          $rating = "Low To Mod";
          break;
        case "low-moderate":
          $rating = "Low - Mod";
          break;
        case "noforecast":
          $rating = "No Forecast";
          break;
        default:
          $rating = "Error Getting Forecast";
          break;
      }
      $data .= '<div class="row">';
      $data .= '<div class="col half">'. $bom_weather_forecast[$item_index+$offset] .'</div>';
      $data .= '<div class="col half text-right">';
      $data .= '<div class="fdr fdr-'.$ratingstr.'" id="fdr_'.$item_index.'">';
      $data .= '<span class="danger-level">'.$rating.'</span>';
      $data .= '</div>';
      $data .= '</div>';
      $data .= '</div>'.$bom_area.'asd';

      $item_index += 1;
    }
  }
  return $data;
}

?>