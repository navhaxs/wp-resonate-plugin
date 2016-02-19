<?php
/**
* Plugin Name: WP Resonate
* Plugin URI: http://navhaxs.au.eu.org/wp-resonate-plugin/
* Description: Integrate Resonate Australia sermons with your WordPress site.
* Version: 1.0
* Author: Jeremy Wong
* Author URI: http://navhaxs.au.eu.org/
* License: GPLv2 or later
*/

const WPRESONATE_STORE = 'wpresonate_store';
const WPRESONATE_CHURCH_ID = 'wpresonate_church_id';
const WPRESONATE_PAGE_ID = 'wpresonate_page_id';
const WPRESONATE_LAST_FETCH = 'wpresonate_last_fetch';
const WPRESONATE_LAST_FETCH_DATA = 'wpresonate_last_fetch_data';
const WPRESONATE_QUERY_SIZE = '50'; // How many search results to request per API query
    
// SHORTCODES
include( plugin_dir_path( __FILE__ ) . 'wpresonate_shortcode.php');
// WIDGETS
include( plugin_dir_path( __FILE__ ) . 'wpresonate_widget.php');
// SETTINGS
include( plugin_dir_path( __FILE__ ) . 'wpresonate_settings.php');

// CRON
//------------ DEACTIVATION
register_deactivation_hook( __FILE__, 'wpresonate_remove_daily_fetch_schedule' );
function wpresonate_remove_daily_fetch_schedule(){
  wp_clear_scheduled_hook( 'wpresonate-fetch-data-daily' );
}

//------------ ACTIVATION
register_activation_hook( __FILE__, 'wpresonate_set_up_options' );

function wpresonate_set_up_options() {
  add_option( WPRESONATE_STORE );
  add_option( WPRESONATE_PAGE_ID );
  add_option( WPRESONATE_CHURCH_ID );
  add_option( WPRESONATE_LAST_FETCH );
  add_option( WPRESONATE_LAST_DATA );

  // Use wp_next_scheduled to check if the event is already scheduled
  $timestamp = wp_next_scheduled( 'wpresonate-fetch-data-daily' );

  // Set up the schedule if it hasn't already been done
  if( $timestamp == false ){
    // Schedule the event for right now, then to repeat daily using the hook 'wpresonate-fetch-data-daily'
    wp_schedule_event( time(), 'daily', 'wpresonate-fetch-data-daily' );
  }
}

// Hook the fetch function into the action wpresonate-fetch-data-daily
add_action( 'wpresonate-fetch-data-daily', 'wpresonate_fetch_data' );
function wpresonate_fetch_data($debug = false){
  // API results will be stored using WP option storage, which the widget will later use to output.
  
  wpresonate_log_me('[wpresonate] Fetch start at '.current_time("d/m/Y H:i:s") );
  
  $series_list = array();
  $series_list_html = '';
  $latest_sermon = '';

  $MAX = 10; // max pages to follow (10 * WPRESONATE_QUERY_SIZE = many, many years worth of sermons)
  $i = 1; // start at page 1
  
  $json = wpresonate_dofetch($i);

  while ($json["pagecnt"] >= $json["page"] && !is_null($json) && $i < $MAX) {
    //wpresonate_log_me ( '[wpresonate] Page: ' . $json["page"] );
    foreach($json['data'] as $item) {
      //wpresonate_log_me ( $item['title'].','.$item['seriesname'] );

      // Save the latest sermon
      if (empty($latest_sermon)) {
          $latest_sermon = $item['title'];
      }

      // Add any new series' to the list
      if (!in_array($item['seriesname'], $series_list)) {
          $series_list[] = $item['seriesname'];
      }
    }

    $i++;
    $json = wpresonate_dofetch($i);
  }
  
  // Format the html output for the widget
  $page_id = get_option ( WPRESONATE_PAGE_ID );
  $page_url = get_permalink ( $page_id );
  foreach($series_list as $item) {
      $series_list_html .= '<li><a href="'.esc_url ($page_url).'?seriesname='.urlencode($item).'">'.$item.'</a></li>';
  }
  
  // Finally, update the widget content
  $last_data = array ($latest_sermon, $series_list);
  $time_now = current_time('timestamp', true); // store time in gmt
  update_option( WPRESONATE_STORE, $series_list_html );    
  update_option( WPRESONATE_LAST_FETCH, $time_now );
  update_option( WPRESONATE_LAST_DATA, $last_data );
  
  wpresonate_log_me('[wpresonate] Fetch completed at '.date('d/m/Y H:i:s', $time_now));
}

function wpresonate_dofetch($page){
    $church_id = get_option( WPRESONATE_CHURCH_ID );
    
    if (empty($church_id)) {
        return null;
    }
    
	$url = 'http://admin.resonate.org.au/api/index.php?order=recorded&sort=DESC&pagesize='.WPRESONATE_QUERY_SIZE.'&keyword=&seriesname=&speakername=&verse=&scripture=null&date=&organisationname=&organisation=' . $church_id;
	if ($page > 1) {
		$url .= '&page=' . $page;
	}
	
	$content = file_get_contents($url);
	$json = json_decode($content, true);
	
	return $json;
}

function wpresonate_log_me($message) {
    if (WP_DEBUG === true) {
        if (is_array($message) || is_object($message)) {
            error_log(print_r($message, true));
        } else {
            error_log($message);
        }
    }
}
