<?php
add_shortcode( 'WPResonateContent', function (){

    // set $url
    $church_id = get_option( 'wpresonate_church_id' );
    $url = "https://admin.resonate.org.au/plugins/allchurchmedia?organisation=".$church_id."&section=.allsermonbox&results=6";
    if (isset( $_GET['seriesname'] )) {
		$url .= "&seriesname=" . urlencode($_GET['seriesname']);
	}    
        
    // output resonate plugin html
    ob_start();
	echo '<script src="'.$url.'"></script>';
	echo '<div class="allsermonbox">';
	echo '<h1 style="text-align:center;"><a href="https://admin.resonate.org.au/all/?organisation=148">Resonate sermon portal</a></h1>';
	echo '<div align="center"><img src="https://admin.resonate.org.au/modules/theme/images/loading2.gif" style="margin-top:15px; width:25px;height:25px" /></div>';
	echo '</div>';
    return ob_get_clean();
});
