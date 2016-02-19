<?php
//------------ ADMIN PAGES ----------------------------------------------------
add_action( 'admin_menu', 'wpresonate_add_admin_menu' );
add_action( 'admin_init', 'wpresonate_settings_init' );

add_action( 'update_option_wpresonate_church_id', 'wpresonate_check_settings', 10, 2 );
add_action( 'update_option_wpresonate_page_id', 'wpresonate_check_settings', 10, 2 );
function wpresonate_check_settings( $old_value, $new_value )
{
	wpresonate_log_me('[wpresonate] Fetch triggered due to user settings update.');

	// run fetch after options change. 
	do_action('wpresonate-fetch-data-daily');

}

function wpresonate_add_admin_menu(  ) { 

	add_menu_page( 'WP Resonate', 'WP Resonate', 'manage_options', 'wp_resonate', 'wpresonate_options_page' );
	add_submenu_page( null, 'WP Resonate Fetch Now', 'Fetch Now', 'manage_options', 'wp_resonate_fetchnow', 'wpresonate_fetchnow_page_callback');


}


function wpresonate_settings_init(  ) { 

	register_setting( 'pluginPage', 'wpresonate_page_id' );
	register_setting( 'pluginPage', 'wpresonate_church_id' );

    add_settings_section(
		'wpresonate_pluginPage_intro_section', 
		__( 'Getting started', 'wordpress' ), 
		'wpresonate_settings_intro_section_callback', 
		'pluginPage'
	);
    
    add_settings_field( 
		'wpresonate_text_field_0', 
		__( 'Organisation ID', 'wordpress' ), 
		'wpresonate_text_field_0_render', 
		'pluginPage', 
		'wpresonate_pluginPage_intro_section' 
	);
	
    add_settings_section(
		'wpresonate_pluginPage_section', 
		__( 'Widget display settings', 'wordpress' ), 
		'wpresonate_settings_section_callback', 
		'pluginPage'
	);

	add_settings_field( 
		'wpresonate_select_field_1', 
		__( 'Sermon page', 'wordpress' ), 
		'wpresonate_select_field_1_render', 
		'pluginPage', 
		'wpresonate_pluginPage_section' 
	);

}


function wpresonate_text_field_0_render(  ) { 

	$church_id = get_option( 'wpresonate_church_id' );
	?>
	<input type='text' name='wpresonate_church_id' value='<?php echo $church_id; ?>'>
    <p class="description">Your church's ID on Resonate, should be a number. See the instructions in your set-up email.</p>

	<?php

}

function wpresonate_select_field_1_render(  ) { 

	$wpresonate_page_id = get_option( 'wpresonate_page_id' );
	?>

    <?php
            
        $args = array(
        'depth'                 => 0,
        'child_of'              => 0,
        'selected'              => $wpresonate_page_id,
        'echo'                  => 1,
        'name'                  => 'wpresonate_page_id',
        'id'                    => null, // string
        'class'                 => null, // string
        'show_option_none'      => null, // string
        'show_option_no_change' => null, // string
        'option_none_value'     => null, // string
        );
        wp_dropdown_pages( $args );
        
        ?>

    <p class="description">Make sure to include the [WPResonateContent] shortcode in this page's content.</p>

<?php

}

function wpresonate_settings_section_callback(  ) { 
    
    $latest_html = '';

    // generate html with results from the latest fetch run.
    $f = get_option( WPRESONATE_LAST_FETCH );
    
    if (empty($f)) {
        $last_fetch = __('Not yet run.', 'wordpress');
        $latest_html = '';
    } else {
	    // the time & date of the last run, in local time
        $last_fetch = get_date_from_gmt(date('Y-m-d H:i:s', get_option( WPRESONATE_LAST_FETCH )));
    
    	// print out the latest fetch results
        $s = get_option( WPRESONATE_STORE );        
        if (!empty($s)) {
            $latest_sermon = get_option( WPRESONATE_LAST_DATA, $last_data);
    	    $latest_sermon_series = get_option( WPRESONATE_LAST_DATA, $last_data);
    
            $latest_html .= '<p class="description">Lastest sermon: '.$latest_sermon[0].'.</p>';
            $latest_html .= '<p class="description">Series: '.$latest_sermon_series[1][0].'</p>';
        } else {
            $latest_html .= '<p class="description" style="color: red;">Warning: Last fetch did not find any sermons!</p>';
        }
    }
    
    // generate html with details of next fetch run.
    $current_time_timestamp = new DateTime('@'.(current_time( 'timestamp', true ))); // in utc
    $next_run_timestamp = new DateTime('@'.wp_next_scheduled( 'wpresonate-fetch-data-daily' )); // in utc
    
    $next_run = get_date_from_gmt(date('Y-m-d H:i:s', wp_next_scheduled( 'wpresonate-fetch-data-daily' ))); // the time & date of the next run, in local time
    
    $time_till_next_run = date_diff($next_run_timestamp, $current_time_timestamp )->format('%h hours %i minutes');

    $safe_url = wp_nonce_url(  get_admin_url(null, '?page=wp_resonate_fetchnow'), 'do' );
    
    echo '<p>'.__( 'The WP Resonate widget displays a list of your sermon series/topics.', 'wordpress' ).'</p>';
      
    echo '<p>'.__( 'The Resonate website is periodically checked for any new changes to your sermon series\' (such as a new preaching topic) to keep this list up-to-date.', 'wordpress' ).'</p>';

    echo '<p class="description">Last update: '.$last_fetch.'.</p>';
    
    echo '<p class="description">Next scheduled update in '. $time_till_next_run . ' ('.$next_run.').</p>';
    
    echo $latest_html;
      
    echo '<p><b>Did you just upload a new sermon series?</b> <a href="'.esc_url ( $safe_url ).'">'.__( 'Force update now', 'wordpress' ).'</a></p>';
    
}

function wpresonate_settings_intro_section_callback(  ) { 

	echo '<div>';

	echo '<p>';

	echo __( 'This plugin integrates Resonate Australia with your WordPress website.', 'wordpress' );

	echo '</p>';

	echo '<p>';

	echo __( 'Use the shortcode ', 'wordpress' ) . '<b>[WPResonateContent]</b>'. __( ' on a page to display sermons.', 'wordpress' );

	echo '</p>';

	echo '</div>';
    
}


function wpresonate_options_page(  ) { 

	$church_id = get_option( 'wpresonate_church_id' );

	if (empty($church_id)) {
		echo '<div class="updated"><h3>Hey there! Welcome to WP Resonate. Make sure you enter your church ID.</h3></div>';
	}

	?>

  <div class="wrap">
    <h1>WP Resonate</h1>

    <form action='options.php' method='post'>
      
      <?php
      settings_fields( 'pluginPage' );
      do_settings_sections( 'pluginPage' );
      submit_button();
      ?>
      
    </form>
    
  </div>

<?php

}

function wpresonate_fetchnow_page_callback( ) {
  
	check_admin_referer( 'do' );
	if (current_user_can('manage_links')) {
	   
		do_action('wpresonate-fetch-data-daily');

		echo '<div class="updated"><h3>Links have now been updated.</h3></div>';

	}

	$url = get_admin_url(null, '?page=wp_resonate');
 
	echo '<div class="wrap">';
	echo '<h1>WP Resonate</h1>';


	echo '<p><a href="'.esc_url ( $url ).'">'.__( '&larr; Return to WP Resonate settings', 'wordpress' ).'</a></p>';

	echo '</div>';

}
