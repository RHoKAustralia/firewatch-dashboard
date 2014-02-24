<?php
if(!defined('FIREWATCH_ROOT_DIR')) define( 'FIREWATCH_ROOT_DIR', dirname(__FILE__) );
include_once(FIREWATCH_ROOT_DIR.'/../functions.php');

if(isset($_GET['independent'])) {
  $district = $_GET['district'];
  echo get_fdr_list($district);
} else {
  function fire_danger_rating($atts) {
    include_once('/../../../../wp-load.php');
    extract(shortcode_atts(array(
      'district' => ''
    ), $atts));
    $content = '<div class="widget-data">'.get_fdr_list($district).'</div>';
    $content .= '<div class="widget-details">';
    $content .= '<a target="_blank" onclick="javascript:_gaq.push([\'_trackEvent\',\'outbound-article\',\'http://www.cfa.vic.gov.au\']);" href="http://www.cfa.vic.gov.au/warnings-restrictions/' .$district. '-fire-district/">CFA Website</a>';
    $content .= '<abbr class="widget-time timeago" title="'.date('r').'">'.date().'</abbr>';
    $content .= '<a class="refresh-widget" data-url="'.plugin_dir_url(__FILE__).basename(__FILE__).'?independent=1&district='. $district .'">Refresh</a></div>';
    return $content;
  }
  add_shortcode('fire_danger_rating', 'fire_danger_rating');
}

function get_fdr_list($district) {
  $data = get_cfa_fdr($district);

  ob_start();
  echo $data;
  $list = ob_get_clean();
  return $list;
}

function get_cfa_fdr($district) {

  $item_index = 0;
  $max_items = 1;
  $data = '';

  $xmlUrl = 'http://www.cfa.vic.gov.au/restrictions/'.$district.'-firedistrict_rss.xml'; // XML feed file/URL
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $xmlUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $output = curl_exec($ch);
  curl_close($ch);

  $xmlObj = simplexml_load_string($output);
  // $arrXml = $xmlObj->objectsIntoArray($xmlObj);
  $arrXml = objectsIntoArray($xmlObj);

  $timezone = 'Australia/Melbourne';
  date_default_timezone_set($timezone);
  $aest = strtotime($arrXml['channel']['pubDate']);

  if (count($arrXml['channel']['item']) == 0) {
    $data = 'No Ratings are available.';
  } else {
  while ($item_index < $max_items) {
    $title = $arrXml['channel']['item'][$item_index]['title'];
    $description = $arrXml['channel']['item'][$item_index]['description'];
    // Get danger level
    $ratingstr = explode('/images/fdr/'.$district.'/', $description);
    $ratingstr = explode('.gif', $ratingstr[1]);
    $ratingstr = explode('_tfb', $ratingstr[0]);
    $ratingstr = $ratingstr[0];

    switch ($ratingstr) {
      case 'codered':
        $rating = 'Code Red';
        break;
      case 'extreme':
        $rating = 'Extreme';
        break;
      case 'severe':
        $rating = 'Severe';
        break;
      case 'veryhigh':
        $rating = 'Very High';
        break;
      case 'high':
        $rating = 'High';
        break;
      case 'lowtomoderate':
        $rating = 'Low To Moderate';
        break;
      case 'low-moderate':
        $rating = 'Low - Moderate';
        break;
      case 'noforecast':
        $rating = 'No Forecast';
        break;
      default:
        $rating = 'Error Getting Forecast';
        break;
    }
    $data .= '<div class="fdr fdr-'.$ratingstr.'" id="fdr-'.$item_index.'">';
    $data .= '<span class="danger-level">'.$rating.'</span>';
    $data .= '</div>';

    $item_index += 1;
  }
  }

  return $data;
}

?>