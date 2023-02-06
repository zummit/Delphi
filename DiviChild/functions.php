<?php

////////////////////////////////////////////////////////////
//  Divi Child functions.php
////////////////////////////////////////////////////////////

// disable xmlrpc
add_filter('xmlrpc_enabled', '__return_false');

// register and enqueue styles and scripts
function theme_enqueue_styles_and_scripts() {
	// enqueue parent styles
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	// register and enqueue child scripts
	wp_register_script( 'custom-scripts', get_stylesheet_directory_uri() . '/scripts/scripts.js', array ( 'jquery' ), 1.1, true);
	wp_enqueue_script( 'custom-scripts');
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles_and_scripts' );

// for caching purposes during development
// https://www.virendrachandak.com/techtalk/how-to-remove-wordpress-version-parameter-from-js-and-css-files/
// remove wp version param from any enqueued scripts
function vc_remove_wp_ver_css_js( $src ) {
    if ( strpos( $src, 'ver=' ) )
        $src = remove_query_arg( 'ver', $src );
    return $src;
}
//add_filter( 'style_loader_src', 'vc_remove_wp_ver_css_js', 9999 );

// Page Slug Body Class
// http://www.wpbeginner.com/wp-themes/how-to-add-page-slug-in-body-class-of-your-wordpress-themes/
function add_slug_body_class( $classes ) {
	global $post;
	if ( isset( $post ) ) {
		$classes[] = $post->post_type . '-' . $post->post_name;
	}
	return $classes;
}
add_filter( 'body_class', 'add_slug_body_class' );

// Remove “Projects” custom post type from Divi
// https://dividezigns.com/handy-divi-snippets/#24
add_filter( 'et_project_posttype_args', 'mytheme_et_project_posttype_args', 10, 1 );
function mytheme_et_project_posttype_args( $args ) {
	return array_merge( $args, array(
		'public'              => false,
		'exclude_from_search' => false,
		'publicly_queryable'  => false,
		'show_in_nav_menus'   => false,
		'show_ui'             => false
	));
}






//======================================================================
// LOUISVILLE TEST
//======================================================================

function get_api_data($atts) {

	$strHTML = "";
	$strFirstDate = "";
	$strLastDate = "";
	$strURL = "https://healthdata.gov/resource/7ctx-gtb7.json?state=KY";

	// make a CURL call to the API
	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => $strURL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_HTTPHEADER => array(
			"X-App-Token: BNfxalGdxwuw9u61zbI9tsT4W"
		),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);

	if ($err) {
		// error
		echo "cURL Error #:" . $err;
	} else {

		// convert JSON to an array of data objects
		$responseObj = json_decode($response);

		// make sure it's not empty
		if(!empty($responseObj)) {

			$intCountRecords = count($responseObj);

			$strHTML .= "<div class=\"mycontainer\">";
			$strHTML .= "<h2 style=\"width: 100%;\">Total Staffed Adult ICU Beds</h2>";
			$strHTML .= "<div class=\"date head\">Collection Period</div>";
			$strHTML .= "<div class=\"beds head\">Total Staffed Adult ICU Beds</div>";

			$i = 0;
			$intStaffQtyTotal = 0;

			// loop over each record
			foreach ($responseObj as $key => $eachObject) {

				//$strState = $eachObject->state;
				$strDate = $eachObject->collection_date;
				//$strDate = date_format($strDate, "F d, Y");	// can't do this because the date is in Floating Timestamp Datatype
				list($strDateRev, $strTime) = explode("T", $strDate);
				list($strYear, $strMonth, $strDay) = explode("-", $strDateRev);
				$strDateRebuilt = $strMonth . "-" . $strDay . "-" . $strYear;

				if($i == 0) {
					$strFirstDate = $strDateRebuilt;
				} elseif ($i == ($intCountRecords-1)) {
					$strLastDate = $strDateRebuilt;
				}

				$strStaffQty = $eachObject->staffed_adult_icu_beds_occupied_est;
				$intStaffQtyTotal = $intStaffQtyTotal + $strStaffQty;

				$strHTML .= "<div class=\"date\">" . $strDateRebuilt . "</div>";
				$strHTML .= "<div class=\"beds\">" . $strStaffQty . "</div>";

				$i++;

			}

			$strHTML .= "<div class=\"date\"><hr/></div>";
			$strHTML .= "<div class=\"beds\"><hr/></div>";

			// output the totals, etc.
			$strHTML .= "<div style=\"width: 100%\">Date Range: ";
			$strHTML .= $strFirstDate . " through " . $strLastDate;
			$strHTML .= "</div>";

			$strHTML .= "<div style=\"width: 100%;\">Average Daily Beds over " . $intCountRecords . " Days: ";
			$strHTML .= number_format($intStaffQtyTotal/$intCountRecords, 1);
			$strHTML .= "</div>";

			$strHTML .= "</div>";

		}

	}

	return $strHTML;

}
add_shortcode("delphi_test", "get_api_data");






//======================================================================
// CUSTOM DASHBOARD
//======================================================================

// ADMIN FOOTER TEXT
function remove_footer_admin () {
    echo "Divi Child Theme by Steve Fry, IR3W Web Services";
}
add_filter('admin_footer_text', 'remove_footer_admin');

// Update CSS within in Admin
//function admin_style() {
//	wp_enqueue_style('admin-styles', get_template_directory_uri().'/admin.css');
//}
//add_action('admin_enqueue_scripts', 'admin_style');

?>
