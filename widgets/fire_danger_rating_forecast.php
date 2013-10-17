<?php
error_reporting(1);
include('/../functions.php');
include('/../../../../wp-load.php');

if(isset($_GET['independent'])) {
  $content = get_fdr_forecast_list();
  echo $content;
} else {
  function fire_danger_rating_forecast($atts) {
    extract(shortcode_atts(array(
      'district' => '',
      'days' => ''
    ), $atts));
    $content = '<div class="widget-data fire-forecast">'.get_fdr_forecast_list($district, $days).'</div>';
    $content .= '<div class="widget-details"><abbr class="widget-time timeago" title="'.date('r').'">'.date().'</abbr><a class="refresh-widget" data-url="'.plugin_dir_url(__FILE__).basename(__FILE__).'?independent=1">Refresh</a></div>';
    return $content;
  }
  add_shortcode('fire_danger_rating_forecast', 'fire_danger_rating_forecast');
}

function get_fdr_forecast_list($district = "central", $days = '3') {
  $data = get_cfa_fdr_forecast($district, $days);

  ob_start();
  echo $data;
  $list = ob_get_clean();
  return $list;
}

function get_cfa_fdr_forecast($district, $days) {

  $ITEM_INDEX = 0;
  $MAX_ITEMS = 3;
  $data = "";

  $xmlUrl = "http://www.cfa.vic.gov.au/restrictions/$district-firedistrict_rss.xml"; // XML feed file/URL

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $xmlUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $output = curl_exec($ch);
  curl_close($ch);

  $xmlObj = simplexml_load_string($output);
  $arrXml = objectsIntoArray($xmlObj);

  $timezone = "Australia/Melbourne";
  date_default_timezone_set($timezone);

  $aest = strtotime($arrXml[channel][pubDate]);
  //echo $arrXml[channel][pubDate];
  if (count($arrXml['channel']['item']) == 0) {
    $data = "No Ratings are available.";
  } else {
  while ($ITEM_INDEX < $MAX_ITEMS) {
    $title = $arrXml[channel][item][$ITEM_INDEX][title];
    $description = $arrXml[channel][item][$ITEM_INDEX][description];
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
        $rating = "Low To Moderate";
        break;
      case "low-moderate":
        $rating = "Low - Moderate";
        break;
      case "noforecast":
        $rating = "No Forecast";
        break;
      default:
        $rating = "Error Getting Forecast";
        break;
    }
    $data .= '<div class="fdr fdr-'.$ratingstr.'" id="fdr_'.$ITEM_INDEX.'">';
    $data .= '<span class="danger-level">'.$rating.'</span>';
    $data .= '</div>';


    // Get danger level
    $ratingstr = explode("/images/fdr/$district/", $description);
    $ratingstr = explode(".gif", $ratingstr[1]);
    $ratingstr = explode("_tfb", $ratingstr[0]);
    $ratingstr = $ratingstr[0];

    $data .= $ratingstr;

    if ($ITEM_INDEX > 0)
      $data .= "<div class='fdr_links' id='fdr_link_fdr_$ITEM_INDEX' onclick='showRating($ITEM_INDEX)'>$title</div>";
    $ITEM_INDEX += 1;
  }
  }
  return $data;
}

?>